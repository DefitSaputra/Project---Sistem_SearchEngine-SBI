<x-app-layout>
    {{-- Header Halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mesin Pencari Korporat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Card untuk Form Pencarian --}}
            <div class="bg-white overflow-hidden border border-gray-200 shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Pencarian Informasi
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Didukung oleh Google API untuk menemukan informasi yang relevan.
                    </p>

                    <form action="{{ route('search.perform') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-3">
                        <input type="text" name="q" placeholder="Ketik kata kunci..." value="{{ $query ?? '' }}" required class="w-full px-4 py-3 border-gray-300 focus:border-sbi-green focus:ring-sbi-green rounded-md shadow-sm transition">
                        
                        <button type="submit" class="bg-sbi-green text-slate-900 font-semibold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-transform duration-200 ease-in-out hover:scale-105 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                            <span>Cari</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Area Hasil Pencarian --}}
            <div class="mt-8">
                @if (isset($results))
                    @if (count($results) > 0 && isset($totalResults))
                         <p class="text-sm text-gray-600 mb-4">
                            Ditemukan sekitar **{{ number_format($totalResults) }}** hasil untuk <span class="font-semibold">"{{ $query }}"</span>
                        </p>
                    @endif
    
                    {{-- Daftar Hasil --}}
                    <div class="space-y-4">
                        @forelse ($results as $item)
                            <div class="bg-white border border-gray-200 overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <a href="{{ $item['link'] }}" target="_blank" class="text-lg font-medium text-blue-700 hover:underline">
                                    {{ $item['title'] }}
                                </a>
                                <p class="text-sm text-green-600 mt-1 truncate">{{ $item['link'] }}</p>
                                <p class="text-gray-600 mt-3 text-sm leading-relaxed">
                                    {{ $item['snippet'] }}
                                </p>
                            </div>
                        @empty
                            @if (isset($query))
                                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg p-10 text-center">
                                    <p class="text-gray-500">Tidak ada hasil ditemukan untuk "{{ $query }}".</p>
                                </div>
                            @endif
                        @endforelse
                    </div>

                    {{-- Paginasi --}}
                    @if (isset($totalPages) && $totalPages > 1)
                        <div class="mt-8">
                            {{-- (Sertakan kode paginasi dari jawaban sebelumnya di sini) --}}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>