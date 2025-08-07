<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - PT Solusi Bangun Indonesia</title>

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
    {{-- Alpine.js diperlukan untuk modal hapus akun --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans" style="background-image: url('{{ asset('images/white-bg.jpg') }}'); background-size: cover; background-attachment: fixed;">

    @include('layouts.navigation')

    <!-- Header Halaman Profil yang Diperbarui -->
    <header class="relative bg-sbi-gray text-white pt-24 pb-16">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ asset('images/bg-sbi4.webp') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-sbi-gray via-sbi-gray/80 to-sbi-green/70"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
            <div class="inline-block bg-white/10 p-4 rounded-full mb-4 ring-4 ring-white/20">
                 <div class="inline-block bg-white/20 p-3 rounded-full">
                    <i class="fas fa-user-shield text-5xl text-white"></i>
                 </div>
            </div>
            <h1 class="text-5xl font-extrabold text-white tracking-tight" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.3);">Profil & Keamanan</h1>
            <p class="mt-4 text-xl text-white/80 max-w-3xl mx-auto">
                Kelola informasi personal dan jaga keamanan akun Anda di sini.
            </p>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Kolom Pengaturan Utama -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Kartu Informasi Profil -->
                    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <!-- Kartu Ubah Kata Sandi -->
                    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Kolom Zona Berbahaya -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Kartu Hapus Akun -->
                    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg border-l-4 border-sbi-red">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </main>

    @include('layouts.footer')
</body>
</html>
