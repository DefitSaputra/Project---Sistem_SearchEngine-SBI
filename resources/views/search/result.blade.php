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
                        'sbi-green': '#8BC34A',
                        'sbi-dark-green': '#689F38',
                        'sbi-gray': '#37474F',
                        'sbi-light-gray': '#607D8B',
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .result-card {
            transition: all 0.3s ease;
        }
        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .search-stats {
            color: #666;
            font-size: 0.9em;
        }
        .result-link {
            color: #1a73e8;
            text-decoration: none;
            font-size: 1.1em;
            font-weight: 500;
        }
        .result-link:hover {
            text-decoration: underline;
        }
        .result-url {
            color: #006621;
            font-size: 0.9em;
        }
        .result-snippet {
            color: #545454;
            line-height: 1.58;
        }
        .pagination-link {
            transition: all 0.3s ease;
        }
        .pagination-link:hover {
            background-color: #f8f9fa;
        }
        .pagination-link.active {
            background-color: #8BC34A;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-8 h-8 bg-sbi-green rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-building text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-sbi-gray">PT Solusi Bangun Indonesia</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="flex items-center space-x-2 text-sbi-gray hover:text-sbi-green transition-colors">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sbi-light-gray hover:text-red-500 transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Header -->
    <div class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center text-sbi-green hover:text-sbi-dark-green transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
                <!-- Search Box -->
                <div class="flex-1 max-w-2xl mx-6">
                    <form action="{{ route('search.perform') }}" method="GET" class="relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ $query }}"
                            placeholder="Cari informasi konstruksi, material, atau layanan..."
                            class="w-full px-4 py-3 border-2 border-sbi-green/20 rounded-full focus:outline-none focus:border-sbi-green focus:ring-4 focus:ring-sbi-green/20 transition-all duration-300">
                        <button 
                            type="submit"
                            class="absolute right-2 top-2 bottom-2 px-6 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-full transition-all duration-300">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="text-right">
                    <div class="text-sm text-sbi-light-gray">
                        <i class="fas fa-clock mr-1"></i>
                        {{ date('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Search Results -->
            <div class="flex-1">
                <!-- Search Statistics -->
                <div class="mb-6">
                    <div class="search-stats">
                        <p>
                            <i class="fas fa-info-circle mr-2"></i>
                            Menampilkan sekitar 
                            <strong>{{ number_format($searchInfo['totalResults'] ?? 0) }}</strong> 
                            hasil untuk "<strong>{{ $query }}</strong>"
                            @if(isset($searchInfo['searchTime']))
                                ({{ number_format($searchInfo['searchTime'], 2) }} detik)
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Results -->
                @if(count($results) > 0)
                    <div class="space-y-6">
                        @foreach($results as $index => $result)
                            <div class="result-card bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6">
                                <!-- Result Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            @if(isset($result['pagemap']['cse_image'][0]['src']))
                                                <img src="{{ $result['pagemap']['cse_image'][0]['src'] }}" 
                                                     alt="Favicon" 
                                                     class="w-4 h-4 rounded">
                                            @else
                                                <i class="fas fa-globe text-gray-400"></i>
                                            @endif
                                            <span class="result-url">{{ $result['displayLink'] }}</span>
                                        </div>
                                        <h3>
                                            <a href="{{ $result['link'] }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="result-link hover:text-sbi-green transition-colors">
                                                {{ $result['title'] }}
                                            </a>
                                        </h3>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <span>#{{ $index + 1 }}</span>
                                        <a href="{{ $result['link'] }}" 
                                           target="_blank"
                                           class="text-sbi-green hover:text-sbi-dark-green transition-colors">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Result Snippet -->
                                <div class="result-snippet">
                                    <p>{{ $result['snippet'] ?? '-' }}</p>
                                </div>

                                <!-- Additional Info -->
                                @if(isset($result['pagemap']['metatags'][0]))
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if(isset($result['pagemap']['metatags'][0]['og:type']))
                                            <span class="inline-block bg-sbi-green/10 text-sbi-green px-2 py-1 rounded-full text-xs">
                                                {{ $result['pagemap']['metatags'][0]['og:type'] }}
                                            </span>
                                        @endif
                                        @if(isset($result['fileFormat']))
                                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                                {{ strtoupper($result['fileFormat']) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12 flex justify-center">
                        <div class="flex items-center space-x-2">
                            @if($currentPage > 1)
                                <a href="{{ route('search.perform') }}?q={{ urlencode($query) }}&start={{ max(1, $currentPage - 10) }}" 
                                   class="pagination-link px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left mr-1"></i>
                                    Sebelumnya
                                </a>
                            @endif

                            @for($i = 1; $i <= min(10, ceil($totalResults / 10)); $i++)
                                @php $start = ($i - 1) * 10 + 1; @endphp
                                <a href="{{ route('search.perform') }}?q={{ urlencode($query) }}&start={{ $start }}" 
                                   class="pagination-link px-4 py-2 border border-gray-300 rounded-lg {{ $start == $currentPage ? 'active' : '' }} transition-colors">
                                    {{ $i }}
                                </a>
                            @endfor

                            @if($currentPage + 10 <= $totalResults)
                                <a href="{{ route('search.perform') }}?q={{ urlencode($query) }}&start={{ $currentPage + 10 }}" 
                                   class="pagination-link px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    Selanjutnya
                                    <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- No Results -->
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
                                <a href="{{ route('dashboard') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-lg transition-colors">
                                    <i class="fas fa-home mr-2"></i>
                                    Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="w-full lg:w-80">
                <!-- Search Tips -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-sbi-gray mb-4">
                        <i class="fas fa-lightbulb text-sbi-green mr-2"></i>
                        Tips Pencarian
                    </h3>
                    <div class="space-y-3 text-sm text-sbi-light-gray">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-quote-left text-sbi-green mt-1"></i>
                            <div>
                                <strong>Gunakan tanda kutip</strong> untuk mencari frasa lengkap
                                <div class="text-xs text-gray-500 mt-1">Contoh: "beton ready mix"</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-plus text-sbi-green mt-1"></i>
                            <div>
                                <strong>Gunakan AND</strong> untuk mencari semua kata
                                <div class="text-xs text-gray-500 mt-1">Contoh: semen AND beton</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-minus text-sbi-green mt-1"></i>
                            <div>
                                <strong>Gunakan minus (-)</strong> untuk mengecualikan kata
                                <div class="text-xs text-gray-500 mt-1">Contoh: konstruksi -apartemen</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-asterisk text-sbi-green mt-1"></i>
                            <div>
                                <strong>Gunakan asterisk (*)</strong> untuk wildcard
                                <div class="text-xs text-gray-500 mt-1">Contoh: konstru*</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-sbi-gray mb-4">
                        <i class="fas fa-link text-sbi-green mr-2"></i>
                        Tautan Cepat
                    </h3>
                    <div class="space-y-3">
                        <a href="{{ route('search.perform') }}?q=produk+semen" 
                           class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-sbi-green/10 rounded-lg transition-colors group">
                            <i class="fas fa-industry text-sbi-green group-hover:text-sbi-dark-green"></i>
                            <span class="text-sbi-gray group-hover:text-sbi-dark-green">Produk Semen</span>
                        </a>
                        <a href="{{ route('search.perform') }}?q=layanan+konstruksi" 
                           class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-sbi-green/10 rounded-lg transition-colors group">
                            <i class="fas fa-hammer text-sbi-green group-hover:text-sbi-dark-green"></i>
                            <span class="text-sbi-gray group-hover:text-sbi-dark-green">Layanan Konstruksi</span>
                        </a>
                        <a href="{{ route('search.perform') }}?q=material+bangunan" 
                           class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-sbi-green/10 rounded-lg transition-colors group">
                            <i class="fas fa-cubes text-sbi-green group-hover:text-sbi-dark-green"></i>
                            <span class="text-sbi-gray group-hover:text-sbi-dark-green">Material Bangunan</span>
                        </a>
                        <a href="{{ route('search.perform') }}?q=proyek+infrastruktur" 
                           class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-sbi-green/10 rounded-lg transition-colors group">
                            <i class="fas fa-road text-sbi-green group-hover:text-sbi-dark-green"></i>
                            <span class="text-sbi-gray group-hover:text-sbi-dark-green">Proyek Infrastruktur</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Searches -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-sbi-gray mb-4">
                        <i class="fas fa-history text-sbi-green mr-2"></i>
                        Pencarian Terkini
                    </h3>
                    <div class="space-y-2" id="recent-searches">
                        <!-- Recent searches will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-sbi-gray text-white py-8 mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-sbi-green rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-building text-white"></i>
                        </div>
                        <span class="text-xl font-bold">SBI Search</span>
                    </div>
                    <p class="text-gray-300 text-sm">Portal pencarian terintegrasi untuk informasi konstruksi dan material bangunan terkini.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Kontak</h4>
                    <div class="text-gray-300 text-sm space-y-1">
                        <p>Jl. TB Simatupang No. 22-26</p>
                        <p>Jakarta 12430</p>
                        <p>Tel: +62 21 29861000</p>
                        <p>Email: info@sbi.co.id</p>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Bantuan</h4>
                    <div class="text-gray-300 text-sm space-y-2">
                        <a href="#" class="block hover:text-sbi-green transition-colors">
                            <i class="fas fa-question-circle mr-2"></i>FAQ
                        </a>
                        <a href="#" class="block hover:text-sbi-green transition-colors">
                            <i class="fas fa-envelope mr-2"></i>Hubungi Kami
                        </a>
                        <a href="#" class="block hover:text-sbi-green transition-colors">
                            <i class="fas fa-shield-alt mr-2"></i>Kebijakan Privasi
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-600 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; 2024 PT Solusi Bangun Indonesia. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Save search query to recent searches
        document.addEventListener('DOMContentLoaded', function() {
            const query = '{{ $query }}';
            if (query) {
                let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
                
                // Remove if already exists
                recentSearches = recentSearches.filter(search => search !== query);
                
                // Add to beginning
                recentSearches.unshift(query);
                
                // Keep only last 5 searches
                recentSearches = recentSearches.slice(0, 5);
                
                localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
                
                // Display recent searches
                displayRecentSearches();
            }
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

        // Call on page load
        displayRecentSearches();
    </script>
</body>
</html>