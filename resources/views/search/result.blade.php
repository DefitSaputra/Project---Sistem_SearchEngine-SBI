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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if ($type === 'map')
        <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style">
        <link rel="preload" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" as="style">
        <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" as="script">
        <link rel="preload" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js" as="script">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    @endif

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
        #map { 
            height: 600px; width: 100%; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 0;
            background-color: #e9e9e9;
        }
        .leaflet-routing-container {
            background-color: white; padding: 10px; border-radius: 8px;
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
                    <form action="{{ route('search.perform') }}" method="GET" class="relative" id="main-search-form">
                        <input type="text" name="q" value="{{ $query }}" placeholder="Cari informasi..." class="w-full px-4 py-3 border-2 border-sbi-green/20 rounded-full focus:outline-none focus:border-sbi-green focus:ring-4 focus:ring-sbi-green/20 transition-all duration-300">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-full transition-all duration-300"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="mt-4 flex justify-between items-center border-b border-gray-200 pb-2">
                        <div class="flex space-x-6 text-sm font-medium text-sbi-gray">
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
                                <a href="{{ route('search.perform', ['q' => $query, 'type' => $key, 'sort_by' => $sortBy]) }}" class="{{ $type == $key ? 'text-sbi-green border-b-2 border-sbi-green' : 'hover:text-sbi-green' }}">
                                    <i class="{{ $value['icon'] }} mr-1"></i> {{ $value['label'] }}
                                </a>
                            @endforeach
                        </div>
                        
                        @if($type !== 'map' && $type !== 'image')
                        @php
                            $sortOptions = [
                                'relevance' => 'Relevansi',
                                'date'      => 'Paling Baru',
                                'date:a'    => 'Paling Lama',
                            ];
                        @endphp
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 text-sm text-sbi-gray bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-md transition">
                                <i class="fas fa-filter text-sbi-green text-xs"></i>
                                <span>{{ isset($sortBy) && is_string($sortBy) && isset($sortOptions[$sortBy]) ? $sortOptions[$sortBy] : 'Urutkan' }}</span>
                                <i class="fas fa-chevron-down text-xs transition-transform" :class="{'rotate-180': open}"></i>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20"
                                 style="display: none;">
                                @foreach($sortOptions as $key => $label)
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'sort_by' => $key]) }}"
                                       class="block px-4 py-2 text-sm text-sbi-gray hover:bg-sbi-green hover:text-white @if($sortBy == $key) bg-sbi-green/10 font-semibold @endif">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-sbi-light-gray"><i class="fas fa-clock mr-1"></i>{{ now()->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                @if(!empty($results) || !empty($mapData))
                    <div class="mb-6">
                        <div class="search-stats">
                                <p><i class="fas fa-info-circle mr-2"></i>Menampilkan sekitar <strong>{{ number_format($totalResults ?? 0) }}</strong> hasil untuk "<strong>{{ $query }}</strong>" @if(isset($searchInfo['searchTime'])) ({{ number_format($searchInfo['searchTime'], 2) }} detik) @endif</p>
                        </div>
                    </div>

                    @if ($type === 'image')
                        {{-- Tampilan gambar tidak berubah --}}
                    @elseif ($type === 'video')
                        {{-- Tampilan video tidak berubah --}}
                    @elseif ($type === 'map')
                        {{-- Tampilan peta tidak berubah --}}
                    @else
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
                                            {{-- ## PERUBAHAN 1: MEMPERBAIKI LOGIKA PENOMORAN ## --}}
                                            <span>#{{ $currentPage + $index }}</span>
                                            <a href="{{ $result['link'] }}" target="_blank" class="text-sbi-green hover:text-sbi-dark-green transition-colors"><i class="fas fa-external-link-alt"></i></a>
                                        </div>
                                    </div>
                                    <div class="result-snippet mt-2">
                                        @php
                                            $publishDate = null;
                                            try {
                                                \Carbon\Carbon::setLocale('id_ID');
                                                $dateSource = $result['pagemap']['metatags'][0] ?? [];
                                                $newsSource = $result['pagemap']['newsarticle'][0] ?? [];

                                                if (isset($dateSource['article:published_time'])) {
                                                    $publishDate = \Carbon\Carbon::parse($dateSource['article:published_time'])->isoFormat('D MMMM YYYY');
                                                } elseif (isset($dateSource['og:updated_time'])) {
                                                    $publishDate = \Carbon\Carbon::parse($dateSource['og:updated_time'])->isoFormat('D MMMM YYYY');
                                                } elseif (isset($newsSource['datepublished'])) {
                                                    $publishDate = \Carbon\Carbon::parse($newsSource['datepublished'])->isoFormat('D MMMM YYYY');
                                                }
                                            } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                                                $publishDate = null;
                                            }
                                        @endphp
                                        @if($publishDate)
                                            <span class="text-sm text-gray-500">{{ $publishDate }} &mdash; </span>
                                        @endif
                                        <p class="inline">{!! $result['htmlSnippet'] ?? $result['snippet'] ?? '-' !!}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($type !== 'map' && !empty($results))
                        {{-- ## PERUBAHAN 2: MEROMBAK TOTAL LOGIKA PAGINATION ## --}}
                        @php
                            // Menghitung nomor halaman saat ini berdasarkan 'start'
                            $pageNumber = floor($currentPage / 10) + 1;
                            // Menghitung total halaman yang tersedia (maksimal 10 karena batasan API)
                            $totalPages = min(10, ceil($totalResults / 10));
                        @endphp
                        <div class="mt-12 flex justify-center">
                            <div class="flex items-center space-x-2">
                                @if($pageNumber > 1)
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'start' => $currentPage - 10, 'sort_by' => $sortBy]) }}" class="pagination-link px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"><i class="fas fa-chevron-left mr-1"></i> Sebelumnya</a>
                                @endif

                                @for($i = 1; $i <= $totalPages; $i++)
                                    @php $start = ($i - 1) * 10 + 1; @endphp
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'start' => $start, 'sort_by' => $sortBy]) }}" class="pagination-link px-4 py-2 border border-gray-300 rounded-lg {{ $pageNumber == $i ? 'active' : '' }} transition-colors">{{ $i }}</a>
                                @endfor

                                @if($pageNumber < $totalPages)
                                    <a href="{{ route('search.perform', ['q' => $query, 'type' => $type, 'start' => $currentPage + 10, 'sort_by' => $sortBy]) }}" class="pagination-link px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Selanjutnya <i class="fas fa-chevron-right ml-1"></i></a>
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

    @if ($type === 'map' && !empty($mapData))
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" async defer></script>
        <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js" async defer></script>

        <script>
            window.addEventListener('load', function () {
                const mapData = @json($mapData);
                const centerPoint = @json($centerPoint);
                const isNearbySearch = Array.isArray(mapData) && mapData.length > 0;
                
                const userLocationIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34],
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    shadowSize: [41, 41]
                });

                if (isNearbySearch) {
                    const map = L.map('map').setView([centerPoint.lat, centerPoint.lon], 14);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker([centerPoint.lat, centerPoint.lon], {icon: userLocationIcon})
                        .addTo(map)
                        .bindPopup('<b>Lokasi Anda Saat Ini</b>')
                        .openPopup();

                    mapData.forEach(place => {
                        L.marker([place.lat, place.lon])
                            .addTo(map)
                            .bindPopup(`<b>${place.display_name}</b>`);
                    });
                } else if (mapData) {
                    const map = L.map('map').setView([mapData.lat, mapData.lon], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                    
                    L.Routing.control({
                        waypoints: [ null, L.latLng(mapData.lat, mapData.lon) ],
                        routeWhileDragging: true,
                        lineOptions: { styles: [{color: '#28a745', opacity: 1, weight: 5}] }
                    }).addTo(map);

                    L.marker([mapData.lat, mapData.lon])
                        .addTo(map)
                        .bindPopup(`<b>{{ $query }}</b><br>${mapData.display_name}`)
                        .openPopup();
                }
            });
        </script>
    @endif

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
    </script>
</body>
</html>
