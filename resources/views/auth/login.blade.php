<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Portal SBI</title>
    {{-- Pastikan Vite berjalan jika Anda menggunakan ini --}}
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
<body class="min-h-screen flex items-center justify-center">

    <!-- Background overlay -->
    <div class="absolute inset-0 bg-gray-900 bg-opacity-60"></div>

    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl px-8 py-10">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/mini-logo.png') }}" alt="Logo SBI" class="h-24">
        </div>

        <!-- Heading -->
        <h2 class="text-center text-2xl font-bold text-gray-700 mb-6">Masuk ke Portal SBI</h2>

        <!-- Session Status (Pesan seperti "password reset link sent") -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            {{-- Token CSRF ini wajib ada dan sudah benar posisinya. --}}
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <x-input-label for="email" value="Email" class="text-gray-700" />
                <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <x-input-label for="password" value="Password" class="text-gray-700" />
                <x-text-input id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-6">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-sbi-green shadow-sm focus:ring-sbi-green" name="remember">
                    <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm text-sbi-green hover:underline" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <x-primary-button class="w-full justify-center bg-sbi-green hover:bg-sbi-dark-green transition-colors py-3">
                {{ __('MASUK') }}
            </x-primary-button>
        </form>

        <!-- Register Link -->
        <div class="mt-6 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-sbi-green hover:underline">Daftar di sini</a>
        </div>
    </div>

</body>
</html>