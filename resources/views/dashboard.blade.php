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
    </style>
</head>
<body class="bg-gray-50">

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
                <form action="{{ route('search.perform') }}" method="GET" class="relative">
                    <div class="relative search-shadow">
                        <input 
                            type="text" 
                            name="q" 
                            placeholder="Cari informasi konstruksi, material, atau layanan..."
                            class="w-full px-6 py-4 text-lg border-2 border-sbi-green/20 rounded-full focus:outline-none focus:border-sbi-green focus:ring-4 focus:ring-sbi-green/20 transition-all duration-300"
                            required>
                        <button 
                            type="submit"
                            class="absolute right-2 top-2 bottom-2 px-6 bg-sbi-green hover:bg-sbi-dark-green text-white rounded-full transition-all duration-300 hover:scale-105">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
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

    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-sbi-gray mb-4">Tentang PT Solusi Bangun Indonesia</h2>
                <p class="text-sbi-light-gray max-w-3xl mx-auto">
                    Perusahaan terdepan dalam industri konstruksi dan material bangunan, 
                    menyediakan solusi inovatif untuk pembangunan infrastruktur berkelanjutan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-sbi-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-industry text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-sbi-gray mb-2">Produksi Material</h3>
                    <p class="text-sbi-light-gray">Memproduksi material bangunan berkualitas tinggi untuk berbagai kebutuhan konstruksi</p>
                </div>

                <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-sbi-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hammer text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-sbi-gray mb-2">Layanan Konstruksi</h3>
                    <p class="text-sbi-light-gray">Penyedia layanan konstruksi terintegrasi dari perencanaan hingga penyelesaian</p>
                </div>

                <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 bg-sbi-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-leaf text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-sbi-gray mb-2">Solusi Berkelanjutan</h3>
                    <p class="text-sbi-light-gray">Mengutamakan pembangunan ramah lingkungan dan berkelanjutan untuk masa depan</p>
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
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed.top-20');
            messages.forEach(msg => {
                msg.style.transition = 'transform 0.3s ease-out';
                msg.style.transform = 'translateX(120%)';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>