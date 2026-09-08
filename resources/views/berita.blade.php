@php
    $appName = $branding['app_name'] ?? 'SIP2M';
    $logoUrl = $branding['logo_url'] ?? asset('images/logo-sip2m.svg');
    $primary = $branding['primary_color'] ?? '#3B5BD9';
    $faviconUrl = $branding['favicon_url'] ?? asset('images/favicon.svg');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $berita->judul }} &mdash; {{ $appName }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($berita->isi), 160) }}">
    <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:'#eef2fe',100:'#dbe3fd',200:'#b9c8fb',300:'#8fa5f6',400:'#6b83ee',
                            500:'{{ $primary }}',600:'{{ $primary }}',700:'{{ $primary }}',
                            800:'#2f43b8',900:'#26327d',950:'#161d4a',
                        },
                        gold: { 400:'#F4B740' },
                    },
                    fontFamily: {
                        sans: ['Inter','ui-sans-serif','system-ui','Segoe UI','Roboto','sans-serif'],
                        display: ['"Plus Jakarta Sans"','Inter','ui-sans-serif','system-ui','sans-serif'],
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        .berita-content { line-height: 1.85; color: #374151; }
        .berita-content p { margin: 0 0 1.1em; }
        .berita-content h2, .berita-content h3 { font-family: "Plus Jakarta Sans", Inter, sans-serif; font-weight: 800; color: #111827; margin: 1.6em 0 .6em; }
        .berita-content ul, .berita-content ol { margin: 0 0 1.1em; padding-left: 1.4em; }
        .berita-content a { color: #3B5BD9; text-decoration: underline; }
        .berita-content img { border-radius: .75rem; margin: 1.2em 0; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 antialiased selection:bg-brand-600 selection:text-white">

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-4xl items-center justify-between gap-4 px-4 md:h-20">
            <a href="{{ route('landing') }}" class="flex shrink-0 items-center">
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-9 w-auto max-w-[200px] object-contain md:h-11">
            </a>
            <a href="{{ route('landing') }}#berita"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:underline">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6"/></svg>
                Kembali ke Beranda
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-10 md:py-14">
        <article>
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-700">
                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> Berita
            </span>
            <h1 class="mt-4 font-display text-2xl font-extrabold leading-tight tracking-tight text-slate-900 md:text-4xl">
                {{ $berita->judul }}
            </h1>
            <time class="mt-3 block text-sm font-medium text-slate-500">
                {{ $berita->tanggal_terbit?->translatedFormat('j F Y') }}
                @if ($berita->author) &middot; {{ $berita->author->name }} @endif
            </time>

            @if ($berita->gambarSampulUrl())
                <img src="{{ $berita->gambarSampulUrl() }}" alt="{{ $berita->judul }}"
                     class="mt-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-sm">
            @endif

            <div class="berita-content mt-8 text-base md:text-[17px]">
                {!! $berita->isi !!}
            </div>
        </article>

        @if ($lainnya->isNotEmpty())
            <div class="mt-16 border-t border-slate-200 pt-10">
                <h2 class="font-display text-lg font-extrabold text-slate-900">Berita Lainnya</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-3">
                    @foreach ($lainnya as $b)
                        <a href="{{ route('berita.show', $b) }}" class="group block overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="aspect-[16/10] overflow-hidden bg-brand-100">
                                @if ($b->gambarSampulUrl())
                                    <img src="{{ $b->gambarSampulUrl() }}" alt="{{ $b->judul }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="p-4">
                                <time class="text-[11px] font-semibold uppercase tracking-wide text-brand-600">{{ $b->tanggal_terbit?->translatedFormat('j F Y') }}</time>
                                <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900 group-hover:text-brand-700">{{ $b->judul }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-14 flex flex-col items-center justify-between gap-4 rounded-2xl bg-brand-950 px-6 py-8 text-center text-white md:flex-row md:text-left">
            <div>
                <h2 class="font-display text-lg font-extrabold">Siap mengajukan usulan?</h2>
                <p class="mt-1 text-sm text-brand-100/85">Masuk dengan akun institusi Anda untuk memulai.</p>
            </div>
            <a href="{{ url('/admin') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-800 shadow-lg transition hover:-translate-y-0.5">
                Masuk ke Sistem
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </main>

    <footer class="mt-10 border-t border-slate-200 bg-white py-8 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ $footer['copyright'] ?: $appName }}. Seluruh hak cipta dilindungi.
    </footer>
</body>
</html>
