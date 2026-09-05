<x-filament-panels::page>
    <div class="mx-auto w-full max-w-md space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Kami telah mengirim kode verifikasi ke email Anda. Masukkan kode tersebut
            untuk menyelesaikan proses masuk. Kode berlaku terbatas dan hanya untuk
            satu kali login.
        </p>

        <form wire:submit="verifikasi" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-4">
                <x-filament::button type="submit">
                    Verifikasi &amp; masuk
                </x-filament::button>

                <x-filament::button type="button" color="gray" outlined wire:click="kirimUlang">
                    Kirim ulang kode
                </x-filament::button>
            </div>
        </form>

        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                Keluar &amp; ganti akun
            </button>
        </form>
    </div>
</x-filament-panels::page>
