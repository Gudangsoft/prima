<x-filament-panels::page>
    <div class="mx-auto w-full max-w-lg space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Sebelum masuk, lengkapi email dan nomor HP aktif Anda. Kode verifikasi (OTP)
            akan dikirim ke email tersebut setiap kali Anda login.
        </p>

        <form wire:submit="simpan" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" wire:target="simpan" wire:loading.attr="disabled">
                Simpan &amp; kirim OTP
            </x-filament::button>
        </form>

        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                Keluar
            </button>
        </form>
    </div>
</x-filament-panels::page>
