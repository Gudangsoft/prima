{{-- Footer --}}
<footer class="bg-slate-900 text-slate-400">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 md:grid-cols-3">
        <div>
            <span class="inline-flex rounded-xl bg-white/95 px-3 py-2 shadow-sm">
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-9 w-auto max-w-[170px] object-contain">
            </span>
            @if ($footer['deskripsi'])
                <p class="mt-5 max-w-xs text-sm leading-relaxed">{{ $footer['deskripsi'] }}</p>
            @endif
        </div>
        <div>
            <h4 class="text-sm font-semibold text-white">Navigasi</h4>
            <ul class="mt-4 space-y-2.5 text-sm">
                @foreach ($nav as [$href, $label])
                    @if ($label !== 'Beranda')
                        <li><a href="{{ $href }}" class="transition hover:text-white">{{ $label }}</a></li>
                    @endif
                @endforeach
                <li><a href="{{ url('/admin') }}" class="transition hover:text-white">Masuk ke Sistem</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-white">Kontak</h4>
            <ul class="mt-4 space-y-2.5 text-sm">
                @if ($footer['lembaga'])<li>{{ $footer['lembaga'] }}</li>@endif
                @if ($footer['alamat'])<li>{{ $footer['alamat'] }}</li>@endif
                @if ($footer['telepon'])<li>{{ $footer['telepon'] }}</li>@endif
                @if ($footer['email'])<li><a href="mailto:{{ $footer['email'] }}" class="transition hover:text-white">{{ $footer['email'] }}</a></li>@endif
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs sm:flex-row">
            <span>&copy; {{ date('Y') }} {{ $footer['copyright'] ?: $appName }}. Seluruh hak cipta dilindungi.</span>
            <span class="font-semibold text-slate-300">{{ $appName }}</span>
        </div>
    </div>
</footer>
