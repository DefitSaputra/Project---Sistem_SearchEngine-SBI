<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Portal SBI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Kalau pakai Vite --}}
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
        <h2 class="text-center text-2xl font-bold text-sbi-gray mb-6">Daftar ke Portal SBI</h2>

        <!-- Register Form -->
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <x-input-label for="name" :value="__('Nama Lengkap')" class="text-sbi-gray" />
                <x-text-input id="name" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="mb-4">
                <x-input-label for="email" :value="__('Email')" class="text-sbi-gray" />
                <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <x-input-label for="password" :value="__('Password')" class="text-sbi-gray" />
                <x-text-input id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-sbi-gray" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-sbi-green focus:ring-sbi-green" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Submit -->
            <x-primary-button class="w-full justify-center bg-sbi-green hover:bg-sbi-dark-green transition-colors">
                {{ __('DAFTAR') }}
            </x-primary-button>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-sbi-green hover:underline">Masuk di sini</a>
        </div>
    </div>

</body>
</html>
