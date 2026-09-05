<x-filament-panels::page>
    <div
        class="mx-auto w-full max-w-md space-y-6"
        @if ($resendCooldown > 0) wire:poll.1s="tickCooldown" @endif
    >
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Kami telah mengirim kode verifikasi ke email Anda. Masukkan kode tersebut
            untuk menyelesaikan proses masuk. Kode berlaku terbatas dan hanya untuk
            satu kali login.
        </p>

        <form wire:submit="verifikasi" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-filament::button type="submit" wire:target="verifikasi" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="verifikasi">Verifikasi &amp; masuk</span>
                    <span wire:loading wire:target="verifikasi">Memverifikasi…</span>
                </x-filament::button>

                <button
                    type="button"
                    wire:click="kirimUlang"
                    wire:loading.attr="disabled"
                    @if ($resendCooldown > 0) disabled @endif
                    class="text-sm font-medium text-primary-600 hover:underline disabled:cursor-not-allowed disabled:opacity-50 dark:text-primary-400"
                >
                    @if ($resendCooldown > 0)
                        Kirim ulang dalam {{ $resendCooldown }} detik
                    @else
                        Kirim ulang kode
                    @endif
                </button>
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
