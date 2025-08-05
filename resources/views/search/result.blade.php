<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian: {{ $query }} - PT Solusi Bangun Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sbi-green': '#28a745',
                        'sbi-dark-green': '#689F38',
                        'sbi-gray': '#37474F',
                        'sbi-light-gray': '#607D8B',
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    {{-- ## PERUBAHAN 1: Tambahkan CSS Leaflet.js untuk Peta ## --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        .result-card { transition: all 0.3s ease; }
        .result-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        .search-stats { color: #666; font-size: 0.9em; }
        .result-link { color: #1a73e8; text-decoration: none; font-size: 1.1em; font-weight: 500; }
        .result-link:hover { text-decoration: underline; }
        .result-url { color: #006621; font-size: 0.9em; }
        .result-snippet { color: #545454; line-height: 1.58; }
        .pagination-link { transition: all 0.3s ease; }
        .pagination-link:hover { background-color: #f8f9fa; }
        .pagination-link.active { background-color: #8BC34A; color: white; border-color: #8BC34A; }
        img { object-fit: cover; }

        /* ## PERUBAHAN 2: Tambahkan style untuk container peta ## */
        #map { 
            height: 500px; /* Peta harus punya tinggi agar terlihat */
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 0; /* Pastikan peta berada di bawah elemen sticky header */
        }
    </style>
</head>
<body class="bg-gray-50">

    @include('layouts.navigation')

    <div class="bg-white border-b sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center text-sbi-green hover:text-sbi-dark-green transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
                <div class="flex-1 max-w-2xl mx-6">
                    <form action="{{ route('search.perform') }}" method="GET" class="relative">
                        <input type="text" name="q" value="{{ $query }}" placeholder="Cari informasi..." class="w-full px-4 py-3 border-2 border-sbi-green/20 rounded-full focus:outline-none focus:border-sbi-green focus:ring-4 focus:ring-sbi-green/20 transition-all duration-300">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-full transition-all duration-300"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="mt-4 flex space-x-6 text-sm font-medium text-sbi-gray border-b border-gray-200 pb-2">
                        @php
                            $types = [
                                'all' => ['label' => 'Semua', 'icon' => 'fas fa-search'],
                                'image' => ['label' => 'Gambar', 'icon' => 'fas fa-image'],
                                'video' => ['label' => 'Video', 'icon' => 'fas fa-video'],
                                'news' => ['label' => 'Berita', 'icon' => 'fas fa-newspaper'],
                                'map' => ['label' => 'Peta', 'icon' => 'fas fa-map-marker-alt'],
                            ];
                        @endphp
                        @foreach ($types as $key => $value)
                            <a href="{{ route('search.perform', ['q' => $query, 'type' => $key]) }}" class="{{ $type == $key ? 'text-sbi-green border-b-2 border-sbi-green' : 'hover:text-sbi-green' }}">
                                <i class="{{ $value['icon'] }} mr-1"></i> {{ $value['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-sbi-light-gray"><i class="fas fa-clock mr-1"></i>{{ date('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                {{-- Cek apakah ada hasil pencarian ATAU ada data peta --}}
                @if(!empty($results) || isset($mapData))
                    <div class="mb-6">
                        <div class="search-stats">
                            <p><i class="fas fa-info-circle mr-2"></i>Menampilkan sekitar <strong>{{ number_format($totalResults ?? 0) }}</strong> hasil untuk "<strong>{{ $query }}</strong>" @if(isset($searchInfo['searchTime'])) ({{ number_format($searchInfo['searchTime'], 2) }} detik) @endif</p>
                        </div>
                    </div>

                    {{-- ## PERUBAHAN 3: Logika Rendering Utama dengan Penambahan Peta ## --}}
                    @if ($type === 'image')
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach($results as $result)
                                <a href="{{ $result['image']['contextLink'] ?? $result['link'] }}" target="_blank" rel="noopener noreferrer" class="block group">
                                    <img src="{{ $result['link'] }}" alt="{{ $result['title'] }}" class="w-full h-40 object-cover rounded-lg shadow-sm group-hover:shadow-md transition-all" onerror="this.style.display='none'">
                                    <p class="mt-2 text-xs text-gray-600 group-hover:text-sbi-green line-clamp-2">{{ $result['title'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    @elseif ($type === 'video')
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            @foreach($results as $result)
                                @php $thumbnail = $result['pagemap']['cse_thumbnail'][0]['src'] ?? 'https://via.placeholder.com/320x180?text=No+Thumbnail'; @endphp
                                <a href="{{ $result['link'] }}" target="_blank" rel="noopener noreferrer" class="group block bg-white rounded-lg shadow hover:shadow-lg overflow-hidden transition-all">
                                    <div class="relative">
                                        <img src="{{ $thumbnail }}" alt="{{ $result['title'] }}" class="w-full h-44 object-cover">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 flex items-center justify-center transition-opacity duration-300">
                                            <i class="fas fa-play-circle text-white text-5xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-sbi-green line-clamp-2">{{ $result['title'] }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">{{ $result['displayLink'] }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @elseif ($type === 'map')
                        {{-- Blok baru untuk menampilkan peta atau pesan 'tidak ditemukan' --}}
                        @if (isset($mapData))
                            <h3 class="text-lg font-semibold text-sbi-gray mb-4">Lokasi ditemukan untuk: <span class="font-bold">"{{ $query }}"</span></h3>
                            <div id="map"></div> {{-- Ini adalah wadah untuk peta --}}
                        @else
                            <div class="text-center py-12 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-map-marked-alt text-6xl text-gray-300"></i>
                                <h3 class="text-xl font-semibold text-sbi-gray mt-4 mb-2">Lokasi Tidak Ditemukan</h3>
                                <p class="text-sbi-light-gray">Maaf, kami tidak dapat menemukan koordinat untuk "{{ $query }}".</p>
                            </div>
                        @endif
                    @else
                        {{-- Ini adalah blok untuk hasil pencarian 'all', 'news', dll. --}}
                        <div class="space-y-6">
                            @foreach($results as $index => $result)
                                <div class="result-card bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                @if(isset($result['pagemap']['cse_image'][0]['src']))
                                                    <img src="{{ $result['pagemap']['cse_image'][0]['src'] }}" alt="Favicon" class="w-4 h-4 rounded">
                                                @else
                                                    <i class="fas fa-globe text-gray-400"></i>
                                                @endif
                                                <span class="result-url">{{ $result['displayLink'] }}</span>
                                            </div>
                                            <h3><a href="{{ $result['link'] }}" target="_blank" rel="noopener noreferrer" class="result-link hover:text-sbi-green transition-colors">{{ $result['title'] }}</a></h3>
                                        </div>
                                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                                            <span>#{{ ($currentPage - 1) * 10 + $index + 1 }}</span>
                                            <a href="{{ $result['link'] }}" target="_blank" class="text-sbi-green hover:text-sbi-dark-green transition-colors"><i class="fas fa-external-link-alt"></i></a>
                                        </div>
                                    </div>
                                    <div class="result-snippet mt-2">
                                        <p>{!! $result['htmlSnippet'] ?? $result['snippet'] ?? '-' !!}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    {{-- ## PERUBAHAN 4: Sembunyikan Paginasi untuk Tampilan Peta ## --}}
                    @if($type !== 'map' && !empty($results))
                        <div class="mt-12 flex justify-center">
                            <div class="flex items-center space-x-2">
                                @if($currentPage > 1)
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'start' => max(1, $currentPage - 10)]) }}" class="pagination-link px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"><i class="fas fa-chevron-left mr-1"></i> Sebelumnya</a>
                                @endif
                                @for($i = 1; $i <= min(10, ceil($totalResults / 10)); $i++)
                                    @php $start = ($i - 1) * 10 + 1; @endphp
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'start' => $start]) }}" class="pagination-link px-4 py-2 border border-gray-300 rounded-lg {{ $start == $currentPage ? 'active' : '' }} transition-colors">{{ $i }}</a>
                                @endfor
                                @if($currentPage + 10 <= $totalResults)
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'start' => $currentPage + 10]) }}" class="pagination-link px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Selanjutnya <i class="fas fa-chevron-right ml-1"></i></a>
                                @endif
                            </div>
                        </div>
                    @endif

                @else
                    <div class="text-center py-12">
                        <div class="mb-6">
                            <i class="fas fa-search text-6xl text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-sbi-gray mb-2">Tidak ada hasil ditemukan</h3>
                        <p class="text-sbi-light-gray mb-6">
                            Maaf, tidak ada hasil yang cocok dengan pencarian "{{ $query }}"
                        </p>
                        <div class="space-y-4">
                            <p class="text-sm text-sbi-light-gray">Saran pencarian:</p>
                            <ul class="text-sm text-sbi-light-gray space-y-2">
                                <li>• Pastikan semua kata dieja dengan benar</li>
                                <li>• Coba gunakan kata kunci yang lebih umum</li>
                                <li>• Coba gunakan sinonim atau kata kunci berbeda</li>
                                <li>• Kurangi jumlah kata kunci</li>
                            </ul>
                            <div class="mt-6">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-lg transition-colors">
                                    <i class="fas fa-home mr-2"></i>
                                    Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar Kanan --}}
            <div class="w-full lg:w-80">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-sbi-gray mb-4">
                        <i class="fas fa-lightbulb text-sbi-green mr-2"></i> Tips Pencarian
                    </h3>
                    <div class="space-y-3 text-sm text-sbi-light-gray">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-quote-left text-sbi-green mt-1"></i>
                            <div><strong>Gunakan tanda kutip</strong> untuk mencari frasa lengkap <div class="text-xs text-gray-500 mt-1">Contoh: "beton ready mix"</div></div>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-minus text-sbi-green mt-1"></i>
                            <div><strong>Gunakan minus (-)</strong> untuk mengecualikan kata <div class="text-xs text-gray-500 mt-1">Contoh: konstruksi -apartemen</div></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-sbi-gray mb-4">
                        <i class="fas fa-link text-sbi-green mr-2"></i> Tautan Cepat
                    </h3>
                    <div class="space-y-3">
                        <a href="{{ route('search.perform') }}?q=produk+semen" class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-sbi-green/10 rounded-lg transition-colors group">
                            <i class="fas fa-industry text-sbi-green group-hover:text-sbi-dark-green"></i>
                            <span class="text-sbi-gray group-hover:text-sbi-dark-green">Produk Semen</span>
                        </a>
                        <a href="{{ route('search.perform') }}?q=layanan+konstruksi" class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-sbi-green/10 rounded-lg transition-colors group">
                            <i class="fas fa-hammer text-sbi-green group-hover:text-sbi-dark-green"></i>
                            <span class="text-sbi-gray group-hover:text-sbi-dark-green">Layanan Konstruksi</span>
                        </a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-sbi-gray mb-4">
                        <i class="fas fa-history text-sbi-green mr-2"></i> Pencarian Terkini
                    </h3>
                    <div class="space-y-2" id="recent-searches"></div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')

    {{-- ## PERUBAHAN 5: Tambahkan JS Leaflet.js sebelum penutup body ## --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const query = '{{ $query ?? '' }}';
            if (query) {
                let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
                recentSearches = recentSearches.filter(search => search !== query);
                recentSearches.unshift(query);
                recentSearches = recentSearches.slice(0, 5);
                localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
            }
            displayRecentSearches();
        });

        function displayRecentSearches() {
            const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            const container = document.getElementById('recent-searches');
            if (recentSearches.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-500">Belum ada pencarian terkini</p>';
                return;
            }
            container.innerHTML = recentSearches.map(search => `
                <a href="{{ route('search.perform') }}?q=${encodeURIComponent(search)}"
                   class="flex items-center space-x-2 p-2 bg-gray-50 hover:bg-sbi-green/10 rounded text-sm text-sbi-gray hover:text-sbi-dark-green transition-colors">
                    <i class="fas fa-search text-xs"></i>
                    <span>${search}</span>
                </a>
            `).join('');
        }

        // ## PERUBAHAN 6: Skrip untuk Inisialisasi Peta ##
        // Skrip ini hanya akan berjalan jika tipe pencarian adalah 'map' dan datanya ditemukan
        @if ($type === 'map' && isset($mapData))
            // Ambil data dari PHP Blade ke JavaScript dengan aman
            const mapData = @json($mapData);

            // Inisialisasi peta di dalam div #map
            // Angka 15 adalah level zoom awal (semakin besar angkanya, semakin dekat)
            const map = L.map('map').setView([mapData.lat, mapData.lon], 15);

            // Tambahkan lapisan peta (tile layer) dari OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Tambahkan penanda (marker) di lokasi yang ditemukan
            const marker = L.marker([mapData.lat, mapData.lon]).addTo(map);

            // Tambahkan popup (info box) pada marker yang menampilkan alamat lengkap
            marker.bindPopup(`<b>{{ $query }}</b><br>${mapData.display_name}`).openPopup();
        @endif
    </script>
</body>
</html>