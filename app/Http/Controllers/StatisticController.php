<?php

namespace App\Http\Controllers;

use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    /**
     * Menampilkan halaman dasbor statistik.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // === METRIK UTAMA ===
        // Mengambil data agregat sederhana. Ini tetap bersih dilakukan di controller.
        $avgSearchTime = SearchLog::query()->average('search_time');
        $totalSearches = SearchLog::query()->count();
        $zeroResultQueries = SearchLog::query()->where('results_count', 0)->count();

        
        // === GRAFIK & TABEL ===
        // Memanggil metode statis dari Model untuk mendapatkan data populer.
        // Jauh lebih bersih daripada menulis query mentah di sini.
        $topQueries = SearchLog::getPopularQueries(30, 10); // Ambil data 30 hari, limit 10

        // Mengambil data distribusi tipe. Query ini cukup spesifik untuk halaman ini.
        $typeDistribution = SearchLog::query()
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(filters, '$.type')) as search_type"), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(filters, '$.type'))")) // Pastikan tipe tidak null
            ->groupBy('search_type')
            ->orderByDesc('total')
            ->get();

        // Mengirim semua data yang sudah diolah ke view
        return view('statistics.index', [
            'avgSearchTime'     => number_format($avgSearchTime, 3),
            'totalSearches'     => $totalSearches,
            'zeroResultQueries' => $zeroResultQueries,
            'topQueries'        => $topQueries,
            'typeDistribution'  => $typeDistribution,
        ]);
    }
}