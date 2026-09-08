@php
    $appName = $branding['app_name'] ?? 'SIP2M';
    $logoUrl = $branding['logo_url'] ?? asset('images/logo-sip2m.svg');
    $primary = $branding['primary_color'] ?? '#3B5BD9';
    $faviconUrl = $branding['favicon_url'] ?? asset('images/favicon.svg');

    $nav = [
        [route('landing').'#beranda', 'Beranda'],
        [route('landing').'#alur', 'Alur Layanan'],
        [route('landing').'#peran', 'Peran Pengguna'],
        [route('landing').'#berita', 'Berita'],
        [route('landing').'#pengumuman', 'Pengumuman'],
    ];

    $shareUrl = url()->current();
    $shareText = rawurlencode($berita->judul);
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
                        accent: { 300:'#c4b5fd',400:'#a78bfa',500:'#8b5cf6',600:'#7c3aed' },
                        gold: { 300:'#f8d99a',400:'#F4B740',500:'#e0a52c' },
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
        #site-header{transition:background-color .3s,box-shadow .3s,border-color .3s}
        #site-header.scrolled{background-color:rgba(255,255,255,.92);backdrop-filter:blur(12px);box-shadow:0 10px 30px -12px rgba(15,23,42,.15);border-color:rgb(226 232 240)}
        .berita-content { line-height: 1.85; color: #374151; }
        .berita-content p { margin: 0 0 1.1em; }
        .berita-content h2, .berita-content h3 { font-family: "Plus Jakarta Sans", Inter, sans-serif; font-weight: 800; color: #111827; margin: 1.6em 0 .6em; }
        .berita-content ul, .berita-content ol { margin: 0 0 1.1em; padding-left: 1.4em; }
        .berita-content a { color: #3B5BD9; text-decoration: underline; }
        .berita-content img { border-radius: .75rem; margin: 1.2em 0; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 antialiased selection:bg-brand-600 selection:text-white">

    @include('partials.site-header', ['nav' => $nav, 'logoUrl' => $logoUrl, 'appName' => $appName])

    <div class="mx-auto max-w-7xl px-4 py-8 md:py-12">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-1.5 text-xs text-slate-500">
            <a href="{{ route('landing') }}" class="hover:text-brand-700 hover:underline">Beranda</a>
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 6 6 6-6 6"/></svg>
            <a href="{{ route('landing') }}#berita" class="hover:text-brand-700 hover:underline">Berita</a>
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 6 6 6-6 6"/></svg>
            <span class="truncate text-slate-400">{{ $berita->judul }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-12">
            {{-- Artikel --}}
            <article class="lg:col-span-8">
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

                {{-- Bagikan (mobile/di bawah artikel) --}}
                <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6 lg:hidden">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Bagikan</span>
                    <a href="https://wa.me/?text={{ $shareText }}%20{{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.3A8.4 8.4 0 0 0 4.6 16.4L4 20l3.7-1A8.4 8.4 0 1 0 17.6 6.3ZM12 18.3a6.7 6.7 0 0 1-3.4-.9l-.2-.1-2.5.7.7-2.4-.2-.3a6.7 6.7 0 1 1 5.6 3Zm3.7-5c-.2-.1-1.2-.6-1.4-.7-.2-.1-.3-.1-.4.1l-.6.7c-.1.2-.2.2-.4.1a5.4 5.4 0 0 1-2.7-2.3c-.2-.3.2-.3.5-.9.1-.1 0-.2 0-.3l-.6-1.5c-.2-.4-.3-.3-.5-.3h-.4a.7.7 0 0 0-.5.2 2.2 2.2 0 0 0-.7 1.6c0 1 .7 1.9.8 2a12 12 0 0 0 4.6 4c1.6.7 1.6.5 1.9.4.3 0 1-.4 1.1-.8.1-.4.1-.7 0-.8Z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5H16l.4-3H13.5V8.4c0-.9.2-1.5 1.5-1.5H16V4.2A20 20 0 0 0 13.9 4c-2.2 0-3.7 1.3-3.7 3.8v2.7H7.8v3h2.4V21h3.3Z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H21l-6.6 7.6L22 21h-6.1l-4.8-6.3L5.6 21H3.5l7-8.1L2.7 3h6.3l4.3 5.8L18.9 3Z"/></svg>
                    </a>
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4">
                <div class="sticky top-24 space-y-6">
                    {{-- Bagikan (desktop) --}}
                    <div class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:block">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Bagikan Artikel</h3>
                        <div class="mt-3 flex items-center gap-2">
                            <a href="https://wa.me/?text={{ $shareText }}%20{{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.3A8.4 8.4 0 0 0 4.6 16.4L4 20l3.7-1A8.4 8.4 0 1 0 17.6 6.3ZM12 18.3a6.7 6.7 0 0 1-3.4-.9l-.2-.1-2.5.7.7-2.4-.2-.3a6.7 6.7 0 1 1 5.6 3Zm3.7-5c-.2-.1-1.2-.6-1.4-.7-.2-.1-.3-.1-.4.1l-.6.7c-.1.2-.2.2-.4.1a5.4 5.4 0 0 1-2.7-2.3c-.2-.3.2-.3.5-.9.1-.1 0-.2 0-.3l-.6-1.5c-.2-.4-.3-.3-.5-.3h-.4a.7.7 0 0 0-.5.2 2.2 2.2 0 0 0-.7 1.6c0 1 .7 1.9.8 2a12 12 0 0 0 4.6 4c1.6.7 1.6.5 1.9.4.3 0 1-.4 1.1-.8.1-.4.1-.7 0-.8Z"/></svg>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5H16l.4-3H13.5V8.4c0-.9.2-1.5 1.5-1.5H16V4.2A20 20 0 0 0 13.9 4c-2.2 0-3.7 1.3-3.7 3.8v2.7H7.8v3h2.4V21h3.3Z"/></svg>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H21l-6.6 7.6L22 21h-6.1l-4.8-6.3L5.6 21H3.5l7-8.1L2.7 3h6.3l4.3 5.8L18.9 3Z"/></svg>
                            </a>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}');this.querySelector('span').textContent='Tersalin!'"
                                    class="inline-flex h-9 items-center gap-1.5 rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 9h10v10H9zM5 15V5h10"/></svg>
                                <span>Salin link</span>
                            </button>
                        </div>
                    </div>

                    {{-- Berita lainnya --}}
                    @if ($lainnya->isNotEmpty())
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Berita Lainnya</h3>
                            <div class="mt-4 space-y-4">
                                @foreach ($lainnya as $b)
                                    <a href="{{ route('berita.show', $b) }}" class="group flex gap-3">
                                        <div class="h-16 w-20 shrink-0 overflow-hidden rounded-lg bg-brand-100">
                                            @if ($b->gambarSampulUrl())
                                                <img src="{{ $b->gambarSampulUrl() }}" alt="{{ $b->judul }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <time class="text-[10px] font-semibold uppercase tracking-wide text-brand-600">{{ $b->tanggal_terbit?->translatedFormat('j M Y') }}</time>
                                            <h4 class="mt-0.5 line-clamp-2 text-sm font-semibold text-slate-800 group-hover:text-brand-700">{{ $b->judul }}</h4>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- CTA --}}
                    <div class="rounded-2xl bg-brand-950 p-6 text-center text-white">
                        <h3 class="font-display text-base font-extrabold">Siap mengajukan usulan?</h3>
                        <p class="mt-1 text-xs text-brand-100/80">Masuk dengan akun institusi Anda.</p>
                        <a href="{{ url('/admin') }}"
                           class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-brand-800 shadow-lg transition hover:-translate-y-0.5">
                            Masuk ke Sistem
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @include('partials.site-footer', ['nav' => $nav, 'logoUrl' => $logoUrl, 'appName' => $appName, 'footer' => $footer])
</body>
</html>
