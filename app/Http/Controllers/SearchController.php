<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Menampilkan halaman pencarian utama.
     */
    public function index(Request $request) // -- PENYESUAIAN -- Menambahkan Request $request
    {
        // -- PENYESUAIAN --
        // Ambil riwayat pencarian dari session untuk ditampilkan di halaman utama.
        $searchHistory = $request->session()->get('search_history', []);

        return view('search.index', [
            'searchHistory' => $searchHistory
        ]);
    }

    /**
     * Menjalankan pencarian dan menyimpan query ke riwayat.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        // Redirect jika query kosong. Nama rute disesuaikan menjadi search.index
        if (empty($query)) {
            return redirect()->route('search.index')->with('error', 'Silakan masukkan kata kunci pencarian.');
        }

        // -- PENYESUAIAN --
        // Logika untuk menyimpan riwayat pencarian ke session.
        // Blok ini dieksekusi setiap kali pencarian valid dilakukan.
        if ($query) {
            $history = $request->session()->get('search_history', []);
            // Hapus query yang sama jika sudah ada (untuk dipindahkan ke depan)
            $history = array_diff($history, [$query]);
            // Tambahkan query baru ke awal array
            array_unshift($history, $query);
            // Batasi riwayat hanya 5 item terakhir
            $history = array_slice($history, 0, 10);
            // Simpan kembali ke session
            $request->session()->put('search_history', $history);
        }
        // -- AKHIR PENYESUAIAN --

        try {
            // Kredensial API Anda (sebaiknya disimpan di file .env)
            $apiKey = "AIzaSyDjitmTVXe1-Q3t5z2vONnzJ8Z0GDAOGbQ";
            $searchEngineId = "2021cf58c69b844ae";
            
            $response = Http::get('https://www.googleapis.com/customsearch/v1', [
                'key' => $apiKey,
                'cx' => $searchEngineId,
                'q' => $query,
                'num' => 10,
                'start' => $request->input('start', 1),
                'sort' => 'date',
                'hl' => 'id',
                'lr' => 'lang_id',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return view('search.result', [
                    'results' => $data['items'] ?? [],
                    'searchInfo' => $data['searchInformation'] ?? [],
                    'query' => $query,
                    'currentPage' => intval($request->input('start', 1)),
                    'totalResults' => $data['searchInformation']['totalResults'] ?? 0,
                ]);
            } else {
                Log::error('Google Search API Error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                // Redirect jika API error. Nama rute disesuaikan menjadi search.index
                return redirect()->route('search.index')->with('error', 'Layanan pencarian tidak tersedia saat ini.');
            }
        } catch (\Exception $e) {
            Log::error('Search Error: ' . $e->getMessage());
            // Redirect jika ada error lain. Nama rute disesuaikan menjadi search.index
            return redirect()->route('search.index')->with('error', 'Terjadi kesalahan saat melakukan pencarian.');
        }
    }
}