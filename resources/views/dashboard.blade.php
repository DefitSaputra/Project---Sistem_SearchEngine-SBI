<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PT Solusi Bangun Indonesia</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sbi-green': '#8BC34A', // Warna hijau asli
                        'sbi-dark-green': '#689F38',
                        'sbi-gray': '#37474F',
                        'sbi-light-gray': '#607D8B',
                        'sbi-bg': '#F7F9FA',
                        'sbi-red': '#F44336', // Warna aksen merah
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
        .hero-gradient {
            background: linear-gradient(135deg, rgba(139, 195, 74, 0.9) 0%, rgba(55, 71, 79, 0.9) 100%);
        }

        .search-shadow {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .bg-pattern {
            background-image:
                radial-gradient(circle at 20% 20%, rgba(139, 195, 74, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(55, 71, 79, 0.1) 0%, transparent 50%);
        }

        /* Styling untuk container saran pencarian */
        #suggestions-container {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-height: 280px;
            overflow-y: auto;
            text-align: left;
        }

        #suggestions-container:empty {
            display: none;
        }

        .suggestion-item {
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .suggestion-item:hover {
            background-color: #f3f4f6;
        }
        
        /* Animasi untuk item misi */
        .mission-item {
            transition: transform 0.3s ease-in-out;
        }
        .mission-item:hover {
            transform: translateX(10px);
        }
        .mission-item .icon-container {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .mission-item:hover .icon-container {
            background-color: #F44336; /* sbi-red */
            transform: rotate(360deg);
        }
        .mission-item .title {
            transition: color 0.3s ease-in-out;
        }
        .mission-item:hover .title {
            color: #8BC34A; /* sbi-green */
        }
    </style>
</head>

<body class="bg-sbi-bg font-sans">

    @include('layouts.navigation')

    <section class="relative min-h-screen flex justify-center pt-20 sm:pt-0 sm:items-center bg-pattern">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gray-900 bg-opacity-60 saturate-50 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-sbi-green/20 via-sbi-gray/30 to-sbi-dark-green/20"></div>
            <div class="absolute top-0 left-0 w-full h-full bg-[url('/images/SBI-bg.jpg')] bg-cover bg-center opacity-20"></div>
            <div class="absolute top-0 right-0 w-1/3 h-full bg-[url('/images/SBI-bg2.jpeg')] bg-cover bg-center opacity-10"></div>
            <div class="absolute bottom-0 left-0 w-1/3 h-full bg-[url('/images/SBI-bg3.jpg')] bg-cover bg-center opacity-10"></div>
        </div>
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-1 animate-float">
                <img src="{{ asset('images/mini-logo.png') }}" alt="Solusi Bangun Indonesia Logo" class="w-32 h-auto mx-auto">
            </div>
            <h1 class="text-4xl md:text-6xl font-bold text-[#263238] mb-4">
                Solusi Bangun Indonesia
                <span class="block text-sbi-green text-shadow-dark">Search Portal</span>
            </h1>
            <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-2xl mx-auto">
                Temukan informasi terkini tentang konstruksi, pembangunan, dan solusi infrastruktur terpercaya
            </p>
            <div class="max-w-2xl mx-auto">
                <form action="{{ route('search.perform') }}" method="GET" class="relative" id="search-form">
                    <div class="relative search-shadow">
                        <input type="text" name="q" id="search-input" placeholder="Cari informasi konstruksi, material, atau layanan..." class="w-full px-6 py-4 text-lg border-2 border-sbi-green/20 rounded-full focus:outline-none focus:border-sbi-green focus:ring-4 focus:ring-sbi-green/20 transition-all duration-300" required autocomplete="off">
                        <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-full transition-all duration-300 hover:scale-105">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="suggestions-container"></div>
                </form>
            </div>
            @if(!empty($searchHistory))
                <div class="mt-8 flex flex-wrap justify-center items-center gap-3">
                    <span class="text-white/90 text-sm">Riwayat Pencarian:</span>
                    @foreach($searchHistory as $item)
                        <a href="{{ route('search.perform', ['q' => $item]) }}" class="bg-white/80 hover:bg-sbi-green hover:text-white px-4 py-2 rounded-full text-sm text-sbi-gray transition-all duration-300">
                            {{ $item }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="py-20 overflow-hidden" style="background-image: url('{{ asset('images/white-bg.jpg') }}'); background-size: cover; background-position: center;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-base font-bold uppercase text-sbi-green tracking-widest">TENTANG KAMI</h2>
                <p class="mt-4 text-5xl font-extrabold text-sbi-gray tracking-tight">Lebih dari 50 Tahun Membangun Indonesia</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 xl:gap-16 items-center">
                <div class="lg:col-span-3">
                    <div class="relative p-8 w-full max-w-xl mx-auto">
                        <div class="absolute inset-0 bg-gradient-to-br from-sbi-green to-sbi-dark-green rounded-3xl transform -rotate-3 transition-transform duration-500 hover:rotate-0 z-0"></div>
                        <div class="relative z-10 bg-black rounded-3xl shadow-2xl overflow-hidden aspect-video">
                            <video class="w-full h-full object-cover" controls autoplay muted loop poster="{{ asset('images/bg-sbi5.jpg') }}">
                                <source src="{{ asset('videos/Company-Profile.mp4') }}" type="video/mp4">
                                Browser Anda tidak mendukung tag video.
                            </video>
                        </div>
                        <div class="absolute top-0 left-0 w-32 h-32 bg-sbi-green/50 z-20" style="clip-path: polygon(0 0, 100% 0, 0 100%);"></div>
                        <div class="absolute bottom-0 right-0 w-32 h-32 bg-sbi-green/50 z-20" style="clip-path: polygon(100% 0, 100% 100%, 0 100%);"></div>
                    </div>
                </div>
                <div class="lg:col-span-2 text-left">
                    <h3 class="text-3xl font-bold text-sbi-gray mb-5">PROFIL PERUSAHAAN</h3>
                    <p class="text-sbi-light-gray leading-relaxed text-lg mb-8">
                        PT Solusi Bangun Indonesia Tbk (SBI) adalah perusahaan publik yang dikelola oleh SIG, menjalankan usaha terintegrasi dari semen, beton siap pakai, agregat, hingga layanan pengelolaan limbah.
                    </p>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <i class="fas fa-industry text-2xl text-sbi-green mt-1 w-8 text-center"></i>
                            <div>
                                <h4 class="font-bold text-sbi-gray">4 Pabrik Strategis</h4>
                                <p class="text-sbi-light-gray text-sm">Tersebar di Narogong, Cilacap, Tuban, dan Lhoknga.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <i class="fas fa-users text-2xl text-sbi-green mt-1 w-8 text-center"></i>
                            <div>
                                <h4 class="font-bold text-sbi-gray">2.000+ Karyawan Profesional</h4>
                                <p class="text-sbi-light-gray text-sm">Menjadi pusat pengembangan sumber daya manusia.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <i class="fas fa-cubes text-2xl text-sbi-green mt-1 w-8 text-center"></i>
                            <div>
                                <h4 class="font-bold text-sbi-gray">14,8 Juta Ton Semen per Tahun</h4>
                                <p class="text-sbi-light-gray text-sm">Total kapasitas produksi untuk memenuhi kebutuhan nasional.</p>
                            </div>
                        </div>
                    </div>
                    <a href="https://solusibangunindonesia.com/tentang-kami/" class="inline-flex items-center mt-10 px-8 py-3 bg-sbi-green text-white font-bold rounded-lg shadow-lg hover:bg-sbi-dark-green transition-all duration-300 transform hover:scale-105">
                        SELENGKAPNYA
                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden bg-gray-50">
        <div class="grid grid-cols-1 lg:grid-cols-2 group">
            <div class="bg-sbi-green flex items-center justify-center p-12 lg:p-20 order-last lg:order-first transition-all duration-500 ease-in-out">
                <div class="max-w-md w-full transition-transform duration-500 ease-in-out group-hover:scale-105">
                    <span class="inline-block bg-sbi-red text-white px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider mb-8 shadow-lg">VISI</span>
                    <h2 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-6" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.3);">
                        Menjadi Perusahaan Penyedia Solusi Bahan Bangunan Terbesar di Regional
                    </h2>
                    <div class="pt-6 border-t-2 border-white/40 opacity-80 group-hover:opacity-100 transition-opacity duration-500">
                        <p class="text-xl text-white font-semibold">Membangun Masa Depan</p>
                        <p class="text-white/80">Infrastruktur berkelanjutan untuk Indonesia</p>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[400px] lg:min-h-[500px] overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 ease-in-out group-hover:scale-110" style="background-image: url('{{ asset('images/bg-sbi7.webp') }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
        </div>

        <div class="hidden lg:flex absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 items-center justify-center">
            <div class="absolute w-28 h-28 bg-white/30 rounded-full animate-ping"></div>
            <div class="w-24 h-24 rounded-full flex items-center justify-center bg-white shadow-2xl backdrop-blur-sm">
                <img src="{{ asset('images/mini-logo.png') }}" alt="Logo SBI" class="w-12 h-auto">
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 group">
            <div class="relative min-h-[400px] lg:min-h-[500px] overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 ease-in-out group-hover:scale-110" style="background-image: url('{{ asset('images/bg-sbi4.webp') }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
            <div class="bg-white flex items-center justify-center p-12 lg:p-20">
                <div class="max-w-md w-full">
                    <span class="inline-block bg-sbi-red text-white px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider mb-8 shadow-lg">MISI</span>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4 mission-item">
                            <div class="flex-shrink-0 w-10 h-10 bg-sbi-green rounded-full flex items-center justify-center text-white text-lg font-bold mt-1 icon-container">1</div>
                            <div>
                                <h3 class="text-xl font-semibold text-sbi-gray title">Kepuasan Pelanggan</h3>
                                <p class="text-sbi-light-gray description">Berorientasi pada kepuasan pelanggan dalam setiap inisiatif bisnis.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 mission-item">
                            <div class="flex-shrink-0 w-10 h-10 bg-sbi-green rounded-full flex items-center justify-center text-white text-lg font-bold mt-1 icon-container">2</div>
                            <div>
                                <h3 class="text-xl font-semibold text-sbi-gray title">Standar Terbaik</h3>
                                <p class="text-sbi-light-gray description">Menerapkan standar terbaik untuk menjamin kualitas.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 mission-item">
                            <div class="flex-shrink-0 w-10 h-10 bg-sbi-green rounded-full flex items-center justify-center text-white text-lg font-bold mt-1 icon-container">3</div>
                            <div>
                                <h3 class="text-xl font-semibold text-sbi-gray title">Lingkungan Berkelanjutan</h3>
                                <p class="text-sbi-light-gray description">Fokus menciptakan perlindungan lingkungan dan tanggung jawab sosial.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 mission-item">
                            <div class="flex-shrink-0 w-10 h-10 bg-sbi-green rounded-full flex items-center justify-center text-white text-lg font-bold mt-1 icon-container">4</div>
                            <div>
                                <h3 class="text-xl font-semibold text-sbi-gray title">Nilai Tambah</h3>
                                <p class="text-sbi-light-gray description">Memberikan nilai tambah terbaik untuk seluruh pemangku kepentingan.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 mission-item">
                            <div class="flex-shrink-0 w-10 h-10 bg-sbi-green rounded-full flex items-center justify-center text-white text-lg font-bold mt-1 icon-container">5</div>
                            <div>
                                <h3 class="text-xl font-semibold text-sbi-gray title">Pengembangan SDM</h3>
                                <p class="text-sbi-light-gray description">Menjadikan sumber daya manusia sebagai pusat pengembangan perusahaan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="py-20 lg:py-32 bg-cover bg-center" style="background-image: url('{{ asset('images/white-bg.jpg') }}');">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24 items-center">
                <div class="relative flex items-center justify-center group w-full h-full">
                    <div class="absolute w-full h-full bg-sbi-green rounded-tl-3xl rounded-br-3xl transform -rotate-3 group-hover:rotate-[-5deg] group-hover:scale-105 transition-all duration-500 ease-in-out"></div>
                    <div class="relative z-10 w-[95%] overflow-hidden rounded-tl-2xl rounded-br-2xl shadow-2xl">
                        <img src="{{ asset('images/bg-sbi6.jpg') }}" alt="Semangat Kerja SBI" class="w-full h-auto object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-sbi-gray/40 to-transparent"></div>
                    </div>
                </div>

                <div class="text-left">
                    <h2 class="text-base font-bold uppercase text-sbi-green tracking-widest mb-2">Semangat Kami</h2>
                    <div class="flex items-center gap-4 mb-6">
                        <img src="{{ asset('images/go-beyond-next.svg') }}" alt="Go Beyond Next" class="h-12 lg:h-16">
                    </div>
                    <p class="text-lg text-sbi-light-gray leading-relaxed mb-8">
                        Sebagai bagian dari SIG, SBI terus bertransformasi untuk beradaptasi pada perubahan dan membangun kondisi kehidupan yang berkelanjutan bagi generasi mendatang. Go Beyond Next mewakili keberanian kami untuk bertindak selangkah lebih maju dan selalu melampaui jangkauan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.footer')

    @if(session('error'))
        <div class="fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <script>
        // Script untuk menghilangkan notifikasi setelah 5 detik
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed.top-20');
            messages.forEach(msg => {
                msg.style.transition = 'transform 0.3s ease-out';
                msg.style.transform = 'translateX(120%)';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);
    </script>
    <script>
        // Script untuk fitur saran pencarian (search suggestion)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const suggestionsContainer = document.getElementById('suggestions-container');
            const searchForm = document.getElementById('search-form');
            let debounceTimer;

            searchInput.addEventListener('input', () => {
                const query = searchInput.value;
                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    suggestionsContainer.innerHTML = '';
                    return;
                }

                // Gunakan debounce untuk menunda request API hingga user berhenti mengetik
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            async function fetchSuggestions(query) {
                try {
                    const response = await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`);
                    const suggestions = await response.json();
                    displaySuggestions(suggestions);
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                    suggestionsContainer.innerHTML = '';
                }
            }

            function displaySuggestions(suggestions) {
                if (suggestions.length === 0) {
                    suggestionsContainer.innerHTML = '';
                    return;
                }
                const suggestionHTML = suggestions.map(suggestion => `
                    <div class="suggestion-item" data-value="${suggestion}">
                        <i class="fas fa-history text-gray-400 mr-3"></i>
                        <span class="text-sbi-gray">${suggestion}</span>
                    </div>
                `).join('');
                suggestionsContainer.innerHTML = suggestionHTML;
            }

            // Handle klik pada item saran
            suggestionsContainer.addEventListener('click', (event) => {
                const suggestionItem = event.target.closest('.suggestion-item');
                if (suggestionItem) {
                    searchInput.value = suggestionItem.dataset.value;
                    suggestionsContainer.innerHTML = '';
                    searchForm.submit();
                }
            });

            // Sembunyikan saran jika klik di luar area pencarian
            document.addEventListener('click', (event) => {
                if (!searchForm.contains(event.target)) {
                    suggestionsContainer.innerHTML = '';
                }
            });
        });
    </script>

</body>
</html>