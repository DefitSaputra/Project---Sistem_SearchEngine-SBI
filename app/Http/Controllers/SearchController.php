<?php

namespace App\Http\Controllers;

use App\Models\SearchLog; // <-- 1. TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SearchController extends Controller
{
    /**
     * Menampilkan halaman utama pencarian (form pencarian).
     */
    public function index(Request $request)
    {
        $searchHistory = $request->session()->get('search_history', []);
        return view('dashboard', ['searchHistory' => $searchHistory]);
    }

    /**
     * Menjalankan semua jenis pencarian dan mencatatnya.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');

        if (empty($query)) {
            return redirect()->route('dashboard')->with('error', 'Silakan masukkan kata kunci pencarian.');
        }

        $this->saveSearchHistory($request, $query);

        $startTime = microtime(true); // <-- 2. CATAT WAKTU MULAI

        try {
            $response = null;
            if ($type === 'map') {
                $response = $this->handleMapSearch($request);
            } else {
                $response = $this->handleGoogleSearch($request);
            }

            // <-- 3. LOGIKA PENCATATAN DATA DIMULAI DI SINI -->
            $endTime = microtime(true);

            // Cek jika response adalah view dan punya data yang relevan
            if ($response instanceof \Illuminate\View\View && isset($response->getData()['totalResults'])) {
                $viewData = $response->getData();
                
                SearchLog::create([
                    'user_id'       => auth()->id(),
                    'query'         => $query,
                    'results_count' => $viewData['totalResults'],
                    'search_time'   => round($endTime - $startTime, 3),
                    'filters'       => ['type' => $type], // Simpan tipe sebagai JSON
                    'ip_address'    => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                ]);
            }
            // <-- AKHIR LOGIKA PENCATATAN -->

            return $response; // <-- 4. KEMBALIKAN RESPONSE ASLI

        } catch (Exception $e) {
            Log::error('Search Controller Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan tidak terduga saat melakukan pencarian.');
        }
    }

    /**
     * Logika baru dan lebih cerdas untuk menangani semua pencarian peta.
     */
    private function handleMapSearch(Request $request)
    {
        $query = $request->input('q');
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $startTime = microtime(true);

        if ($lat && $lon) {
            $places = $this->findPlacesNearby($query, $lat, $lon);
            return view('search.result', [
                'query'        => $query,
                'type'         => 'map',
                'mapData'      => $places,
                'centerPoint'  => ['lat' => $lat, 'lon' => $lon],
                'totalResults' => count($places),
                'results'      => [],
                'searchInfo'   => [],
                'currentPage'  => 1,
            ]);
        }

        $parts = $this->parseQuery($query);
        $what = $parts['what'];
        $where = $parts['where'];

        if ($where) {
            $locationCoords = $this->geocodeLocation($where . ', Indonesia');
            if ($locationCoords) {
                $places = $this->findPlacesNearby($what, $locationCoords['lat'], $locationCoords['lon']);
                if (!empty($places)) {
                    return view('search.result', [
                        'query'        => $query,
                        'type'         => 'map',
                        'mapData'      => $places,
                        'centerPoint'  => $locationCoords,
                        'totalResults' => count($places),
                        'results'      => [],
                        'searchInfo'   => [],
                        'currentPage'  => 1,
                    ]);
                }
            }
        }

        $location = $this->geocodeLocation($query . ', Indonesia');
        return view('search.result', [
            'query'        => $query,
            'type'         => 'map',
            'mapData'      => $location ? [$location] : [], // Pastikan mapData selalu array
            'centerPoint'  => $location,
            'totalResults' => $location ? 1 : 0,
            'results'      => [],
            'searchInfo'   => [],
            'currentPage'  => 1,
        ]);
    }

    /**
     * Memanggil Overpass API untuk mencari tempat di sekitar koordinat.
     */
    private function findPlacesNearby($query, $lat, $lon, $radius = 10000)
    {
        $overpassQuery = "[out:json][timeout:50];(node['name'~'{$query}',i](around:{$radius},{$lat},{$lon});way['name'~'{$query}',i](around:{$radius},{$lat},{$lon}););out center;";
        
        $response = Http::timeout(60)->asForm()->withHeaders([
            'User-Agent' => config('app.name') . '/1.0 (sbi@example.com)'
        ])->post('https://overpass-api.de/api/interpreter', ['data' => $overpassQuery]);

        $places = [];
        if ($response->successful()) {
            foreach ($response->json()['elements'] as $element) {
                if (isset($element['tags']['name']) && (isset($element['lat']) || isset($element['center']['lat']))) {
                    $places[] = [
                        'lat' => $element['lat'] ?? $element['center']['lat'],
                        'lon' => $element['lon'] ?? $element['center']['lon'],
                        'display_name' => $element['tags']['name'],
                        'tags' => $element['tags'] ?? [],
                    ];
                }
            }
        }
        return $places;
    }

    /**
     * Memanggil Nominatim API untuk mengubah alamat menjadi koordinat.
     */
    private function geocodeLocation($address)
    {
        $response = Http::timeout(60)->withHeaders([
            'User-Agent' => config('app.name') . '/1.0 (sbi@example.com)'
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $address, 'format' => 'json', 'addressdetails' => 1, 'limit' => 1
        ]);

        if ($response->successful() && !empty($response->json())) {
            $location = $response->json()[0];
            return [
                'lat' => $location['lat'],
                'lon' => $location['lon'],
                'display_name' => $location['display_name'],
                'tags' => $location['address'] ?? [],
            ];
        }
        return null;
    }

    private function parseQuery($query)
    {
        $prepositions = ['di', 'in', 'near', 'dekat'];
        $query = str_ireplace($prepositions, '', $query);
        $commonLocations = ['jakarta', 'bandung', 'surabaya', 'semarang', 'yogyakarta', 'medan', 'makassar', 'cilacap', 'purwokerto'];
        foreach ($commonLocations as $location) {
            if (str_contains(strtolower($query), $location)) {
                return [
                    'what' => trim(str_ireplace($location, '', $query)),
                    'where' => $location
                ];
            }
        }
        return ['what' => $query, 'where' => null];
    }

    private function handleGoogleSearch(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all'); // Default ke 'all'
        $apiKey = env('GOOGLE_API_KEY');
        $searchEngineId = env('SEARCH_ENGINE_ID');

        if (!$apiKey || !$searchEngineId) {
            Log::error('Kredensial Google API tidak ditemukan di file .env');
            return redirect()->route('dashboard')->with('error', 'Konfigurasi layanan pencarian belum lengkap.');
        }

        $searchParams = [
            'key' => $apiKey, 'cx' => $searchEngineId, 'q' => $query,
            'num' => 10, 'start' => $request->input('start', 1),
            'hl' => 'id', 'lr' => 'lang_id',
        ];

        switch ($type) {
            case 'image': $searchParams['searchType'] = 'image'; break;
            case 'news': $searchParams['sort'] = 'date'; break;
            case 'video': $searchParams['q'] = "site:youtube.com " . $query; break;
        }

        $response = Http::get('https://www.googleapis.com/customsearch/v1', $searchParams);

        if ($response->successful()) {
            $data = $response->json();
            return view('search.result', [
                'results'      => $data['items'] ?? [],
                'searchInfo'   => $data['searchInformation'] ?? [],
                'query'        => $query,
                'type'         => $type,
                'currentPage'  => intval($request->input('start', 1)),
                'totalResults' => (int)($data['searchInformation']['totalResults'] ?? 0),
                'mapData'      => null,
                'centerPoint'  => null,
            ]);
        }
        
        Log::error('Google Search API Error', ['status' => $response->status(), 'response' => $response->body()]);
        return redirect()->route('dashboard')->with('error', 'Layanan pencarian tidak tersedia saat ini.');
    }

    private function saveSearchHistory(Request $request, $query)
    {
        $history = $request->session()->get('search_history', []);
        $history = array_diff($history, [$query]);
        array_unshift($history, $query);
        $request->session()->put('search_history', array_slice($history, 0, 10));
    }
    
    public function getSuggestions(Request $request)
    {
        $request->validate(['q' => 'required']);
        $query = strtolower($request->input('q'));
        $searchHistory = $request->session()->get('search_history', []);
        $suggestions = array_filter($searchHistory, function ($item) use ($query) {
            return str_contains(strtolower($item), $query);
        });
        $suggestions = array_slice(array_values($suggestions), 0, 5);
        return response()->json($suggestions);
    }
}
