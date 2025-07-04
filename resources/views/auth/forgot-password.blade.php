<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Portal SBI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-image: url('{{ asset('images/SBI-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-900 bg-opacity-70">

    <!-- Background overlay -->
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gray-900 bg-opacity-60"></div>
    </div>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl px-8 py-10 backdrop-blur-sm relative z-10">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/mini-logo.png') }}" alt="Logo SBI" class="h-24">
        </div>

        <!-- Heading -->
        <h2 class="text-center text-2xl font-bold text-sbi-gray mb-4">Lupa Password</h2>

        <p class="text-sm text-gray-600 mb-6 text-center">
            Masukkan email Anda, dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Form -->
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email -->
            <div class="mb-6">
                <x-input-label for="email" :value="__('Email')" class="text-sbi-gray" />
                <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green"
                              type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Submit -->
            <x-primary-button class="w-full justify-center bg-sbi-green hover:bg-sbi-dark-green transition-colors">
                {{ __('Kirim Tautan Reset Password') }}
            </x-primary-button>
        </form>

        <!-- Back to login -->
        <div class="mt-6 text-center text-sm text-gray-600">
            Kembali ke halaman <a href="{{ route('login') }}" class="text-sbi-green hover:underline">Login</a>
        </div>
    </div>

</body>
</html>
