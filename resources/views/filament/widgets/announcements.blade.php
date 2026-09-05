<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-megaphone" icon-color="primary">
        <x-slot name="heading">Pengumuman</x-slot>

        <div class="grid gap-4 md:grid-cols-3">
            @forelse ($this->getAnnouncements() as $a)
                <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">
                        {{ $a['tanggal'] }}
                    </p>
                    <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $a['judul'] }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $a['isi'] }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pengumuman.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
