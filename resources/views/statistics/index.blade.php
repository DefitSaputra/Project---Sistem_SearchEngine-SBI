<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistik - PT Solusi Bangun Indonesia</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sbi-green': '#8BC34A',
                        'sbi-dark-green': '#689F38',
                        'sbi-gray': '#37474F',
                        'sbi-light-gray': '#607D8B',
                        'sbi-bg': '#F4F7F9',
                        'sbi-red': '#F44336',
                    },
                    fontFamily: {
                        'sans': ['"Segoe UI"', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', '"Open Sans"', '"Helvetica Neue"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-shadow {
            box-shadow: 0 8px 25px rgba(55, 71, 79, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-shadow:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(55, 71, 79, 0.12);
        }
        .stat-card-icon {
            transition: transform 0.3s ease;
        }
        .stat-card:hover .stat-card-icon {
            transform: scale(1.1) rotate(-5deg);
        }
    </style>
</head>

<body class="font-sans" style="background-image: url('{{ asset('images/white-bg.jpg') }}'); background-size: cover; background-attachment: fixed;">

    @include('layouts.navigation')

    <!-- Header Halaman Statistik dengan Background Gambar -->
    <header class="relative bg-sbi-gray text-white pt-24 pb-32">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ asset('images/bg-sbi7.webp') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-sbi-gray via-sbi-gray/80 to-sbi-green/60"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
            <i class="fas fa-chart-line text-5xl text-sbi-green mb-4"></i>
            <h1 class="text-5xl font-extrabold text-white tracking-tight">Dashboard Analitik</h1>
            <p class="mt-4 text-xl text-white/80 max-w-3xl mx-auto">
                Wawasan mendalam dari setiap interaksi. Pahami tren, ukur performa, dan temukan peluang baru.
            </p>
        </div>
    </header>

    <!-- Konten Utama dengan Negative Margin dan Z-Index yang Diperbaiki -->
    <main class="relative z-20 -mt-20 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Grid Kartu Metrik Utama yang Diperbarui -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl card-shadow flex items-center gap-6 stat-card">
                    <div class="w-16 h-16 rounded-full bg-sbi-green/10 flex items-center justify-center stat-card-icon">
                        <i class="fas fa-search text-3xl text-sbi-green"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-sbi-light-gray uppercase tracking-wider">Total Pencarian</p>
                        <p class="text-4xl font-bold text-sbi-gray">{{ number_format($totalSearches) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl card-shadow flex items-center gap-6 stat-card">
                    <div class="w-16 h-16 rounded-full bg-sbi-green/10 flex items-center justify-center stat-card-icon">
                        <i class="fas fa-tachometer-alt text-3xl text-sbi-green"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-sbi-light-gray uppercase tracking-wider">Waktu Respon</p>
                        <p class="text-4xl font-bold text-sbi-gray">{{ $avgSearchTime }} <span class="text-xl font-medium">dtk</span></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl card-shadow flex items-center gap-6 stat-card">
                    <div class="w-16 h-16 rounded-full bg-sbi-red/10 flex items-center justify-center stat-card-icon">
                        <i class="fas fa-times-circle text-3xl text-sbi-red"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-sbi-light-gray uppercase tracking-wider">Pencarian Gagal</p>
                        <p class="text-4xl font-bold text-sbi-gray">{{ number_format($zeroResultQueries) }}</p>
                    </div>
                </div>
            </div>

            <!-- Grid untuk Grafik dengan "No Data" State -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mt-12">
                <!-- Grafik Utama (Top Queries) -->
                <div class="lg:col-span-3 bg-white rounded-xl card-shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-sbi-gray">Kueri Paling Populer (30 Hari)</h3>
                        <p class="text-sm text-sbi-light-gray">Kata kunci yang paling sering dicari oleh pengguna.</p>
                    </div>
                    <div class="p-6">
                        @if($topQueries->isEmpty())
                            <div class="flex items-center justify-center h-80 text-center text-sbi-light-gray">
                                <div>
                                    <i class="fas fa-info-circle text-4xl mb-4 opacity-50"></i>
                                    <p class="font-semibold">Belum ada data yang cukup.</p>
                                    <p class="text-sm">Grafik akan muncul setelah ada lebih banyak aktivitas pencarian.</p>
                                </div>
                            </div>
                        @else
                            <div class="h-96">
                                <canvas id="topQueriesChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Grafik Sekunder (Distribusi Tipe) -->
                <div class="lg:col-span-2 bg-white rounded-xl card-shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-sbi-gray">Distribusi Pencarian</h3>
                        <p class="text-sm text-sbi-light-gray">Proporsi jenis pencarian yang dilakukan.</p>
                    </div>
                    <div class="p-6">
                        @if($typeDistribution->isEmpty())
                            <div class="flex items-center justify-center h-80 text-center text-sbi-light-gray">
                                <div>
                                    <i class="fas fa-pie-chart text-4xl mb-4 opacity-50"></i>
                                    <p class="font-semibold">Data distribusi belum tersedia.</p>
                                </div>
                            </div>
                        @else
                            <div class="h-96 flex items-center justify-center">
                                <canvas id="typeDistributionChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </main>

    @include('layouts.footer')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Mengambil data dari controller
        const topQueriesData = @json($topQueries);
        const typeDistributionData = @json($typeDistribution);

        // Inisialisasi Chart.js dengan desain yang lebih baik
        if (topQueriesData.length > 0) {
            const ctxBar = document.getElementById('topQueriesChart').getContext('2d');
            const gradient = ctxBar.createLinearGradient(0, 0, 0, 350);
            gradient.addColorStop(0, 'rgba(139, 195, 74, 0.8)');
            gradient.addColorStop(1, 'rgba(139, 195, 74, 0.2)');

            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: topQueriesData.map(row => row.query),
                    datasets: [{
                        label: 'Jumlah Pencarian',
                        data: topQueriesData.map(row => row.search_count),
                        backgroundColor: gradient,
                        borderColor: '#689F38',
                        borderWidth: 2,
                        borderRadius: 5,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        if (typeDistributionData.length > 0) {
            new Chart(document.getElementById('typeDistributionChart'), {
                type: 'doughnut',
                data: {
                    labels: typeDistributionData.map(row => (row.search_type || 'Lainnya').charAt(0).toUpperCase() + (row.search_type || 'Lainnya').slice(1)),
                    datasets: [{
                        data: typeDistributionData.map(row => row.total),
                        backgroundColor: ['#8BC34A', '#37474F', '#F44336', '#607D8B', '#FFC107'],
                        borderColor: '#F4F7F9',
                        borderWidth: 4,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { padding: 20 } } }
                }
            });
        }
    </script>

</body>
</html>
