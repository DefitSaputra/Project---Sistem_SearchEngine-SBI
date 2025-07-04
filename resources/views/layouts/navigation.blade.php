<!-- Navigation -->
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="/">
                        <img src="{{ asset('images/logo-sbi.png') }}" alt="Solusi Bangun Indonesia Logo" class="block h-9 w-auto">
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 text-sbi-gray hover:text-sbi-green transition-colors">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </a>

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
