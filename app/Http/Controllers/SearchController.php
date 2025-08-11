<?php

namespace App\Http\Controllers;

use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $searchHistory = $request->session()->get('search_history', []);
        return view('dashboard', ['searchHistory' => $searchHistory]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');

        if (empty($query)) {
            return redirect()->route('dashboard')->with('error', 'Silakan masukkan kata kunci pencarian.');
        }

        $this->saveSearchHistory($request, $query);

        $startTime = microtime(true);

        try {
            $response = null;
            if ($type === 'map') {
                $response = $this->handleMapSearch($request);
            } else {
                $response = $this->handleGoogleSearch($request);
            }

            $endTime = microtime(true);

            if ($response instanceof \Illuminate\View\View && isset($response->getData()['totalResults'])) {
                $viewData = $response->getData();
                
                SearchLog::create([
                    'user_id'       => Auth::id(),
                    'query'         => $query,
                    'results_count' => $viewData['totalResults'],
                    'search_time'   => round($endTime - $startTime, 3),
                    'filters'       => $request->only(['type', 'sort_by']),
                    'ip_address'    => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                ]);
            }

            return $response;

        } catch (Exception $e) {
            Log::error('Search Controller Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan tidak terduga saat melakukan pencarian.');
        }
    }

    private function handleMapSearch(Request $request)
    {
        $query = $request->input('q');
        $lat = $request->input('lat');
        $lon = $request->input('lon');

        // ## PERBAIKAN: PASTIKAN sortBy DIKIRIM KE VIEW ##
        $sortBy = $request->input('sort_by', 'relevance');

        if ($lat && $lon) {
            $places = $this->findPlacesNearby($query, $lat, $lon);
            return view('search.result', [
                'query'        => $query, 'type'         => 'map', 'mapData'      => $places,
                'centerPoint'  => ['lat' => $lat, 'lon' => $lon], 'totalResults' => count($places),
                'results'      => [], 'searchInfo'   => [], 'currentPage'  => 1, 'sortBy' => $sortBy
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
                        'query'        => $query, 'type'         => 'map', 'mapData'      => $places,
                        'centerPoint'  => $locationCoords, 'totalResults' => count($places),
                        'results'      => [], 'searchInfo'   => [], 'currentPage'  => 1, 'sortBy' => $sortBy
                    ]);
                }
            }
        }

        $location = $this->geocodeLocation($query . ', Indonesia');
        return view('search.result', [
            'query'        => $query, 'type'         => 'map', 'mapData'      => $location ? [$location] : [],
            'centerPoint'  => $location, 'totalResults' => $location ? 1 : 0,
            'results'      => [], 'searchInfo'   => [], 'currentPage'  => 1, 'sortBy' => $sortBy
        ]);
    }

    private function findPlacesNearby($query, $lat, $lon, $radius = 10000)
    {
        $cacheKey = 'overpass_' . md5($query . $lat . $lon . $radius);
        $cacheDuration = now()->addHours(6);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($query, $lat, $lon, $radius) {
            Log::info("CACHE MISS: Calling Overpass API for query: {$query}");
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
        });
    }

    private function geocodeLocation($address)
    {
        $cacheKey = 'geocode_' . md5($address);
        $cacheDuration = now()->addDays(30);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($address) {
            Log::info("CACHE MISS: Calling Nominatim API for address: {$address}");
            $response = Http::timeout(60)->withHeaders([
                'User-agent' => config('app.name') . '/1.0 (sbi@example.com)'
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
        });
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
        $type = $request->input('type', 'all');
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

        // ## PERBAIKAN: LOGIKA SORT YANG BENAR ##
        $sortBy = $request->input('sort_by', 'relevance');
        
        // Google API menggunakan 'date' untuk Paling Baru (descending)
        // dan tidak memiliki parameter untuk Paling Lama (ascending).
        // Jadi kita hanya menambahkan parameter sort jika 'Paling Baru' dipilih.
        if ($sortBy === 'date') {
            $searchParams['sort'] = 'date';
        }
        
        switch ($type) {
            case 'image': $searchParams['searchType'] = 'image'; break;
            case 'news':
                // Untuk tipe berita, selalu urutkan berdasarkan tanggal
                $searchParams['sort'] = 'date';
                break;
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
                'sortBy'       => $sortBy,
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
