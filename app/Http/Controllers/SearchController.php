<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SearchController extends Controller
{
    /**
     * Menampilkan halaman utama pencarian (form pencarian).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ambil riwayat pencarian dari session untuk ditampilkan di halaman utama.
        $searchHistory = $request->session()->get('search_history', []);

        // Tampilkan view 'dashboard' dengan data riwayat pencarian.
        return view('dashboard', [
            'searchHistory' => $searchHistory
        ]);
    }

    /**
     * Menjalankan pencarian, menyimpan query ke riwayat,
     * dan menangani berbagai tipe pencarian (web, gambar, video, dll.).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function search(Request $request)
    {
        // Ambil input 'q' (query) dan 'type' dari request.
        $query = $request->input('q');
        $type = $request->input('type', 'all'); // Default ke 'all' jika tidak ada.

        // Jika query kosong, kembalikan ke halaman utama dengan pesan error.
        if (empty($query)) {
            return redirect()->route('dashboard')->with('error', 'Silakan masukkan kata kunci pencarian.');
        }

        // Simpan query pencarian yang valid ke dalam session history.
        $history = $request->session()->get('search_history', []);
        $history = array_diff($history, [$query]); // Hapus query yang sama jika sudah ada (untuk dipindahkan ke depan).
        array_unshift($history, $query); // Tambahkan query baru ke awal array.
        $history = array_slice($history, 0, 10); // Batasi riwayat hanya 10 item terakhir.
        $request->session()->put('search_history', $history); // Simpan kembali riwayat yang sudah diperbarui ke session.

        try {
            // ===================================================================
            // ## BLOK BARU: Penanganan Khusus untuk Pencarian Peta (Nominatim) ##
            // ===================================================================
            if ($type === 'map') {
                // Buat query yang lebih spesifik untuk hasil di Indonesia
                $locationQuery = $query . ' Indonesia';

                $response = Http::withHeaders([
                    // PENTING: Nominatim membutuhkan User-Agent. Ganti dengan info aplikasi Anda.
                    'User-Agent' => config('app.name') . '/1.0 (email-kontak-anda@example.com)'
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $locationQuery,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'limit' => 1 // Ambil 1 hasil teratas saja
                ]);

                $mapData = null;
                // Jika request berhasil dan hasilnya tidak kosong
                if ($response->successful() && !empty($response->json())) {
                    $location = $response->json()[0];
                    $mapData = [
                        'lat' => $location['lat'],
                        'lon' => $location['lon'],
                        'display_name' => $location['display_name'],
                    ];
                }

                // Kirim data ke view 'search.result'
                return view('search.result', [
                    'query' => $query,
                    'type' => $type,
                    'mapData' => $mapData, // Data spesifik untuk peta
                    'results' => [], // Tidak ada 'items' untuk tipe peta
                    'searchInfo' => [],
                    'currentPage' => 1, // Peta tidak memiliki paginasi
                    'totalResults' => $mapData ? 1 : 0, // Hasilnya 1 jika lokasi ditemukan
                ]);
            }
            // ===============================================
            // ## AKHIR BLOK BARU: Penanganan Peta Selesai ##
            // ===============================================

            // ## LOGIKA LAMA ANDA: Pencarian menggunakan Google API (untuk 'all', 'image', 'news', 'video') ##
            // Logika ini hanya akan berjalan jika $type BUKAN 'map'.
            
            // Ambil kredensial dari file .env untuk keamanan.
            $apiKey = env('GOOGLE_API_KEY');
            $searchEngineId = env('SEARCH_ENGINE_ID');

            // Jika salah satu kredensial tidak ada, redirect dengan error.
            if (!$apiKey || !$searchEngineId) {
                Log::error('Kredensial Google API tidak ditemukan di file .env');
                return redirect()->route('dashboard')->with('error', 'Konfigurasi layanan pencarian belum lengkap.');
            }

            // Siapkan parameter dasar untuk panggilan API.
            $searchParams = [
                'key' => $apiKey,
                'cx' => $searchEngineId,
                'q' => $query,
                'num' => 10, // Jumlah hasil per halaman.
                'start' => $request->input('start', 1), // Index halaman untuk paginasi.
                'hl' => 'id', // Bahasa antarmuka (Bahasa Indonesia).
                'lr' => 'lang_id', // Batasi hasil ke dokumen berbahasa Indonesia.
            ];

            // Modifikasi parameter berdasarkan tipe pencarian yang diminta.
            switch ($type) {
                case 'image':
                    $searchParams['searchType'] = 'image';
                    break;
                case 'news':
                    $searchParams['sort'] = 'date'; // Sortir berita berdasarkan tanggal.
                    break;
                case 'video':
                    // Membatasi pencarian ke YouTube untuk hasil video yang lebih relevan.
                    $searchParams['q'] = "site:youtube.com " . $query;
                    break;
            }

            // Lakukan panggilan ke Google Custom Search API.
            $response = Http::get('https://www.googleapis.com/customsearch/v1', $searchParams);

            // Jika panggilan API berhasil (status code 2xx).
            if ($response->successful()) {
                $data = $response->json();

                // Tampilkan view 'search.result' dengan data hasil pencarian.
                return view('search.result', [
                    'results' => $data['items'] ?? [],
                    'searchInfo' => $data['searchInformation'] ?? [],
                    'query' => $query,
                    'type' => $type,
                    'currentPage' => intval($request->input('start', 1)),
                    'totalResults' => $data['searchInformation']['totalResults'] ?? 0,
                    'mapData' => null, // Tidak ada data peta untuk tipe ini
                ]);
            } else {
                // Jika panggilan API gagal (misal: kuota habis, error 4xx/5xx).
                Log::error('Google Search API Error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->route('dashboard')->with('error', 'Layanan pencarian tidak tersedia. Mungkin kuota harian telah tercapai.');
            }
        } catch (Exception $e) {
            // Jika terjadi error lain (misal: masalah koneksi).
            Log::error('Search Controller Exception: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan tidak terduga saat melakukan pencarian.');
        }
    }

    // ================================================================
    // ## METHOD BARU: Untuk menyediakan data Search Suggestions ##
    // ================================================================
    /**
     * Menyediakan saran pencarian berdasarkan input pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuggestions(Request $request)
    {
        // Validasi untuk memastikan ada parameter 'q' dalam request
        $request->validate(['q' => 'required']);

        $query = strtolower($request->input('q'));

        // Ambil riwayat pencarian dari session sebagai sumber data
        $searchHistory = $request->session()->get('search_history', []);

        // Filter riwayat pencarian: cari item yang 'mengandung' query dari pengguna.
        // str_contains() akan return true jika $item mengandung $query.
        $suggestions = array_filter($searchHistory, function ($item) use ($query) {
            return str_contains(strtolower($item), $query);
        });

        // Batasi jumlah saran menjadi 5 teratas dan reset keys pada array
        $suggestions = array_slice(array_values($suggestions), 0, 5);

        // Kembalikan hasilnya dalam format JSON
        return response()->json($suggestions);
    }
}