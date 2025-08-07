<section class="space-y-6">
    <header>
        <h2 class="text-2xl font-bold text-sbi-gray">
            Hapus Akun
        </h2>

        <p class="mt-2 text-sbi-light-gray">
            Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-sbi-red hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500"
    >{{ __('Hapus Akun') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Apakah Anda yakin ingin menghapus akun Anda?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Setelah akun Anda dihapus, semua datanya akan hilang selamanya. Silakan masukkan kata sandi Anda untuk mengonfirmasi.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Password" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 rounded-lg border-gray-300 focus:border-sbi-red focus:ring-sbi-red"
                    placeholder="Password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="ms-3 bg-sbi-red hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500">
                    {{ __('Hapus Akun') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
