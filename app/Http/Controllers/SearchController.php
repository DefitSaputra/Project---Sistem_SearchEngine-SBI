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
        // Hapus query yang sama jika sudah ada (untuk dipindahkan ke depan).
        $history = array_diff($history, [$query]);
        // Tambahkan query baru ke awal array.
        array_unshift($history, $query);
        // Batasi riwayat hanya 10 item terakhir.
        $history = array_slice($history, 0, 10);
        // Simpan kembali riwayat yang sudah diperbarui ke session.
        $request->session()->put('search_history', $history);

        try {
            // Ambil kredensial dari file .env untuk keamanan.
            // Pastikan Anda sudah mengaturnya di file .env
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
                    // Untuk berita, kita bisa menambahkan filter situs berita terkemuka jika diperlukan.
                    // Contoh: $searchParams['q'] = $query . " site:detik.com OR site:kompas.com";
                    // Untuk saat ini, kita biarkan pencarian berita secara umum.
                    $searchParams['sort'] = 'date'; // Sortir berita berdasarkan tanggal.
                    break;
                case 'video':
                     // Membatasi pencarian ke YouTube untuk hasil video yang lebih relevan.
                    $searchParams['q'] = "site:youtube.com " . $query;
                    break;
                // 'map' dan 'all' (default) tidak memerlukan parameter khusus.
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
                    'type' => $type, // Kirim tipe ke view untuk menandai tab aktif.
                    'currentPage' => intval($request->input('start', 1)),
                    'totalResults' => $data['searchInformation']['totalResults'] ?? 0,
                ]);
            } else {
                // Jika panggilan API gagal (misal: kuota habis, error 4xx/5xx).
                // Catat error ke log untuk debugging.
                Log::error('Google Search API Error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                // Kembalikan ke halaman utama dengan pesan error yang ramah.
                return redirect()->route('dashboard')->with('error', 'Layanan pencarian tidak tersedia saat ini. Mungkin kuota harian telah tercapai.');
            }
        } catch (Exception $e) {
            // Jika terjadi error lain (misal: masalah koneksi).
            Log::error('Search Controller Exception: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan tidak terduga saat melakukan pencarian.');
        }
    }
}