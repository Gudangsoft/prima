{{-- Header --}}
<header id="site-header" class="sticky top-0 z-40 border-b border-transparent bg-white/70 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 h-16 md:h-20 flex items-center justify-between gap-4">
        <a href="{{ route('landing') }}" class="flex items-center shrink-0">
            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 md:h-14 w-auto max-w-[240px] object-contain">
        </a>

        <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
            @foreach ($nav as [$href, $label])
                <a href="{{ $href }}" class="rounded-lg px-3 py-2 transition hover:bg-brand-50 hover:text-brand-700">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ url('/admin') }}"
               class="hidden sm:inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-brand-600 to-accent-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:shadow-xl hover:shadow-brand-600/30 hover:-translate-y-0.5">
                Masuk ke Sistem
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <button id="menu-btn" type="button" class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100" aria-label="Menu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3">
        @foreach ($nav as [$href, $label])
            <a href="{{ $href }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">{{ $label }}</a>
        @endforeach
        <a href="{{ url('/admin') }}" class="mt-1 block rounded-lg bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white">Masuk ke Sistem</a>
    </div>
</header>

<script>
    (function () {
        var header = document.getElementById('site-header');
        var onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 8); };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        var btn = document.getElementById('menu-btn');
        var menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', function () { menu.classList.toggle('hidden'); });
            menu.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { menu.classList.add('hidden'); });
            });
        }
    })();
</script>
