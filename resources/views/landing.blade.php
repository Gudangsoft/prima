@php
    $alur = [
        ['draft', 'Usulan Baru', 'Dosen menyusun usulan: pilih skema, isi substansi, unggah proposal PDF.', '#94a3b8'],
        ['submitted', 'Pengajuan', 'Usulan dikirim ke LPPM untuk diperiksa kelengkapannya.', '#3b82f6'],
        ['approved_lppm', 'Persetujuan Institusi', 'Admin LPPM / Pimpinan menyetujui usulan yang layak lanjut.', '#6366f1'],
        ['under_review', 'Penilaian Reviewer', 'Reviewer yang ditugaskan memberi skor dan rekomendasi.', '#8b5cf6'],
        ['funded', 'Penetapan Pendanaan', 'LPPM menetapkan status pendanaan dan nomor SK.', '#22c55e'],
        ['in_progress', 'Pelaksanaan & Monev', 'Kegiatan berjalan; dosen mengunggah laporan kemajuan.', '#14b8a6'],
        ['reported', 'Pelaporan Akhir', 'Laporan akhir diserahkan dan diverifikasi.', '#0ea5e9'],
        ['output_validated', 'Validasi Luaran', 'Bukti luaran (publikasi/HKI/produk) divalidasi LPPM.', '#f59e0b'],
    ];

    $peran = [
        ['Dosen', 'Mengajukan usulan, memantau status, mengunggah laporan & bukti luaran.', 'M12 14c3.31 0 6-2.69 6-6s-2.69-6-6-6-6 2.69-6 6 2.69 6 6 6Zm0 2c-4 0-12 2-12 6v2h24v-2c0-4-8-6-12-6Z'],
        ['Admin LPPM', 'Mengelola skema & periode, menyetujui usulan, menugaskan reviewer, menetapkan pendanaan.', 'M12 2 3 6v6c0 5.25 3.75 9.75 9 11 5.25-1.25 9-5.75 9-11V6l-9-4Zm0 4 5 2.2V12c0 3.7-2.4 6.9-5 7.8-2.6-.9-5-4.1-5-7.8V8.2L12 6Z'],
        ['Reviewer', 'Menilai usulan yang ditugaskan, memberi skor dan rekomendasi kelayakan.', 'M4 4h16v2H4V4Zm0 5h16v2H4V9Zm0 5h10v2H4v-2Zm12.5 6L13 16.5l1.5-1.5 2 2 4-4L22 14.5 16.5 20Z'],
        ['Pimpinan / Ketua LPPM', 'Pengawasan menyeluruh: persetujuan final institusi dan rekap capaian.', 'M3 13h2v7H3v-7Zm4-4h2v11H7V9Zm4-6h2v17h-2V3Zm4 9h2v8h-2v-8Zm4-4h2v12h-2V8Z'],
    ];

    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $appName = $branding['app_name'] ?? 'SIP2M';
    $logoUrl = $branding['logo_url'] ?? asset('images/logo-sip2m.svg');
    $primary = $branding['primary_color'] ?? '#3B5BD9';
    $heroTitle = $branding['hero_title'] ?: 'Kelola usulan penelitian & pengabdian dalam satu alur terpadu';
    $heroSubtitle = $branding['hero_subtitle'] ?: 'Dari pengajuan usulan oleh dosen, persetujuan pimpinan, penilaian reviewer, penetapan pendanaan, hingga monitoring dan validasi luaran — semuanya tercatat dan terpantau.';
    $faviconUrl = $branding['favicon_url'] ?? asset('images/favicon.svg');

    $nav = [
        ['#beranda', 'Beranda'],
        ['#alur', 'Alur Layanan'],
        ['#peran', 'Peran Pengguna'],
        ['#berita', 'Berita'],
        ['#pengumuman', 'Pengumuman'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} &mdash; Sistem Informasi Penelitian &amp; Pengabdian</title>
    <meta name="description" content="Portal pengelolaan usulan penelitian dan pengabdian kepada masyarakat untuk lingkungan LPPM/LP2M.">
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
                        gold:   { 300:'#f8d99a',400:'#F4B740',500:'#e0a52c' },
                    },
                    fontFamily: {
                        sans: ['Inter','ui-sans-serif','system-ui','Segoe UI','Roboto','sans-serif'],
                        display: ['"Plus Jakarta Sans"','Inter','ui-sans-serif','system-ui','sans-serif'],
                    },
                    keyframes: {
                        drift: { '0%,100%':{transform:'translate3d(0,0,0) scale(1)'}, '50%':{transform:'translate3d(0,-28px,0) scale(1.08)'} },
                        driftx:{ '0%,100%':{transform:'translate3d(0,0,0)'}, '50%':{transform:'translate3d(34px,18px,0)'} },
                        shimmer:{ '0%':{backgroundPosition:'0% 50%'}, '100%':{backgroundPosition:'200% 50%'} },
                        floaty: { '0%,100%':{transform:'translateY(0) rotate(-1.5deg)'}, '50%':{transform:'translateY(-14px) rotate(-1.5deg)'} },
                    },
                    animation: {
                        drift:'drift 14s ease-in-out infinite',
                        driftx:'driftx 18s ease-in-out infinite',
                        shimmer:'shimmer 6s linear infinite',
                        floaty:'floaty 7s ease-in-out infinite',
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak]{display:none}
        .grid-lines{
            background-image:linear-gradient(rgba(255,255,255,.055) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,.055) 1px,transparent 1px);
            background-size:48px 48px;
            -webkit-mask-image:radial-gradient(ellipse 80% 70% at 50% 0%,#000 40%,transparent 100%);
                    mask-image:radial-gradient(ellipse 80% 70% at 50% 0%,#000 40%,transparent 100%);
        }
        .noise{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.5'/%3E%3C/svg%3E")}
        .gradient-text{
            background:linear-gradient(100deg,#ffffff 0%,#dbe6ff 35%,#f8d99a 75%,#ffffff 100%);
            background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;
            animation:shimmer 6s linear infinite;
        }
        .reveal{opacity:0;transform:translateY(22px);transition:opacity .7s cubic-bezier(.22,1,.36,1),transform .7s cubic-bezier(.22,1,.36,1)}
        .reveal.in{opacity:1;transform:none}
        .reveal[data-d="1"]{transition-delay:.08s}.reveal[data-d="2"]{transition-delay:.16s}
        .reveal[data-d="3"]{transition-delay:.24s}.reveal[data-d="4"]{transition-delay:.32s}
        #site-header{transition:background-color .3s,box-shadow .3s,border-color .3s}
        #site-header.scrolled{background-color:rgba(255,255,255,.92);backdrop-filter:blur(12px);box-shadow:0 10px 30px -12px rgba(15,23,42,.15);border-color:rgb(226 232 240)}
        @media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}.reveal{opacity:1;transform:none}}
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 antialiased selection:bg-brand-600 selection:text-white">

    {{-- Top strip --}}
    <div class="bg-brand-950 text-brand-100/80 text-[11px] sm:text-xs">
        <div class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between gap-3">
            <span class="truncate">{{ $footer['lembaga'] ?: 'Lembaga Penelitian & Pengabdian kepada Masyarakat' }}</span>
            <span class="shrink-0 inline-flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-gold-400 animate-pulse"></span>
                Tahun Anggaran {{ $tahunAktif }}
            </span>
        </div>
    </div>

    {{-- Header --}}
    <header id="site-header" class="sticky top-0 z-40 border-b border-transparent bg-white/70 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 h-16 md:h-20 flex items-center justify-between gap-4">
            <a href="#beranda" class="flex items-center shrink-0">
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

    {{-- Hero --}}
    <section id="beranda" class="relative overflow-hidden bg-brand-950 text-white">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 grid-lines"></div>
            <div class="absolute -left-32 -top-24 h-[26rem] w-[26rem] rounded-full bg-brand-500/35 blur-3xl animate-drift"></div>
            <div class="absolute right-[-10rem] top-10 h-[24rem] w-[24rem] rounded-full bg-accent-500/30 blur-3xl animate-driftx"></div>
            <div class="absolute left-1/3 bottom-[-12rem] h-[22rem] w-[22rem] rounded-full bg-gold-400/20 blur-3xl animate-drift"></div>
            <div class="absolute inset-0 noise opacity-[.03]"></div>
        </div>

        <div id="hero-slider" class="relative h-[560px] sm:h-[600px] md:h-[680px]">
            @forelse ($heroSlides as $i => $slide)
                <div class="hero-slide absolute inset-0 {{ $i === 0 ? 'z-10 opacity-100' : 'z-0 opacity-0' }} transition-opacity duration-1000 ease-in-out">
                    <img src="{{ $slide->gambarSampulUrl() }}" alt="{{ $slide->judul }}" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-950/55 to-brand-950/10"></div>
                    <div class="absolute inset-0 flex items-end">
                        <div class="reveal mx-auto w-full max-w-7xl px-4 pb-32 sm:pb-36 md:pb-40">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-medium ring-1 ring-inset ring-white/20 backdrop-blur">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> Berita
                            </span>
                            <h1 class="mt-4 max-w-3xl font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl md:text-5xl">{{ $slide->judul }}</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-brand-100/85 md:text-base">
                                {{ \Illuminate\Support\Str::limit(strip_tags($slide->isi), 140) }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="hero-slide absolute inset-0 z-10 opacity-100">
                    <div class="absolute inset-0 flex items-end">
                        <div class="reveal mx-auto w-full max-w-7xl px-4 pb-32 sm:pb-36 md:pb-40">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-medium ring-1 ring-inset ring-white/20 backdrop-blur">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> Platform internal LPPM &mdash; alur kerja bergaya BIMA
                            </span>
                            <h1 class="mt-4 max-w-3xl font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl md:text-5xl">
                                <span class="gradient-text">{{ $heroTitle }}</span>
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-brand-100/85 md:text-base">
                                {{ $heroSubtitle }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse

            {{-- Overlay tetap: CTA & indikator, tidak ikut memudar antar slide --}}
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20">
                <div class="mx-auto max-w-7xl px-4 pb-8 sm:pb-10">
                    <div class="pointer-events-auto flex flex-wrap gap-3">
                        <a href="{{ url('/admin') }}"
                           class="group inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-brand-800 shadow-xl shadow-black/20 transition hover:-translate-y-0.5 hover:shadow-2xl">
                            Masuk / Ajukan Usulan
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a href="#alur"
                           class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-6 py-3.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/25 backdrop-blur transition hover:bg-white/15">
                            Pelajari Alur Layanan
                        </a>
                    </div>

                    @if ($heroSlides->count() > 1)
                        <div class="pointer-events-auto mt-6 flex items-center gap-2">
                            @foreach ($heroSlides as $i => $slide)
                                <button type="button" data-slide-dot="{{ $i }}"
                                        class="h-2 rounded-full transition-all {{ $i === 0 ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/70' }}"
                                        aria-label="Slide {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($heroSlides->count() > 1)
                <button type="button" id="hero-slider-prev" aria-label="Sebelumnya"
                        class="absolute left-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/10 p-2.5 text-white ring-1 ring-inset ring-white/20 backdrop-blur transition hover:bg-white/20 sm:left-6">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6"/></svg>
                </button>
                <button type="button" id="hero-slider-next" aria-label="Berikutnya"
                        class="absolute right-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/10 p-2.5 text-white ring-1 ring-inset ring-white/20 backdrop-blur transition hover:bg-white/20 sm:right-6">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            @endif
        </div>

        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-50 to-transparent"></div>
    </section>

    {{-- Stats --}}
    <section class="relative z-10 -mt-14">
        <div class="mx-auto max-w-7xl px-4">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-6">
                @foreach ([
                    ['Total Usulan', (int) $stats['usulan'], true, 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01'],
                    ['Usulan Didanai', (int) $stats['didanai'], true, 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
                    ['Dosen Terdaftar', (int) $stats['dosen'], true, 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                    ['Reviewer', (int) $stats['reviewer'], true, 'M9 11H5a2 2 0 0 0-2 2v7h6M15 7h4a2 2 0 0 1 2 2v11h-6M9 11V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6'],
                    ['Skema Aktif', (int) $stats['skema_aktif'], true, 'M4 4h7v7H4zM13 4h7v7h-7zM13 13h7v7h-7zM4 13h7v7H4z'],
                    ['Dana Tersalur', $rp($stats['dana']), false, 'M2 10h20M6 15h4M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z'],
                ] as $i => [$label, $value, $isNum, $icon])
                    <div class="reveal rounded-2xl border border-slate-200/70 bg-white/80 p-4 text-center shadow-sm ring-1 ring-white/50 backdrop-blur transition hover:-translate-y-1 hover:shadow-lg" data-d="{{ min($i + 1, 4) }}">
                        <span class="mx-auto flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                        </span>
                        <div @if ($isNum) data-count="{{ $value }}" @endif
                             class="mt-2.5 break-words text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">{{ $isNum ? number_format($value, 0, ',', '.') : $value }}</div>
                        <div class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Alur --}}
    <section id="alur" class="py-20 md:py-28">
        <div class="mx-auto max-w-7xl px-4">
            <div class="reveal max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Siklus Usulan</span>
                <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Alur Layanan</h2>
                <p class="mt-3 text-slate-600">Delapan tahap dari draf hingga luaran tervalidasi. Setiap perpindahan status tercatat otomatis pada riwayat usulan.</p>
            </div>

            <div class="relative mt-14">
                <div class="pointer-events-none absolute left-0 right-0 top-5 hidden h-px bg-gradient-to-r from-transparent via-brand-300 to-transparent lg:block"></div>
                <ol class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($alur as $i => [$key, $judul, $desc, $color])
                        <li class="reveal group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl" data-d="{{ ($i % 4) + 1 }}">
                            <div class="flex items-center gap-3">
                                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold text-white shadow-lg"
                                      style="background:linear-gradient(135deg,{{ $color }},{{ $color }}cc)">
                                    {{ $i + 1 }}
                                    <span class="absolute inset-0 rounded-xl ring-2 ring-inset ring-white/25"></span>
                                </span>
                                <h3 class="font-semibold leading-tight text-slate-900">{{ $judul }}</h3>
                            </div>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $desc }}</p>
                            <span class="mt-4 inline-block rounded-md bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">{{ $key }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- Peran --}}
    <section id="peran" class="border-y border-slate-200 bg-white py-20 md:py-28">
        <div class="mx-auto max-w-7xl px-4">
            <div class="reveal max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Hak Akses</span>
                <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Peran Pengguna</h2>
                <p class="mt-3 text-slate-600">Akses dan wewenang dibatasi per peran. Satu akun dapat memegang lebih dari satu peran.</p>
            </div>
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($peran as $i => [$nama, $desc, $path])
                    <div class="reveal group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-transparent hover:shadow-xl hover:ring-1 hover:ring-brand-200" data-d="{{ $i + 1 }}">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-accent-600 text-white shadow-lg shadow-brand-600/25 transition group-hover:scale-105">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $nama }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Berita --}}
    <section id="berita" class="bg-white py-20 md:py-28">
        <div class="mx-auto max-w-7xl px-4">
            <div class="reveal flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Sorotan</span>
                    <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Berita</h2>
                    <p class="mt-3 text-slate-600">Liputan kegiatan, capaian, dan momen penting LPPM.</p>
                </div>
                <a href="#berita" class="hidden text-sm font-semibold text-brand-600 hover:underline sm:inline-flex">Lihat semua &rarr;</a>
            </div>
            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @forelse ($berita as $i => $b)
                    <article class="reveal group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl" data-d="{{ $i + 1 }}">
                        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-accent-600">
                            @if ($b->gambarSampulUrl())
                                <img src="{{ $b->gambarSampulUrl() }}" alt="{{ $b->judul }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-12 w-12 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 5h16v14H4V5Zm3 3h6m-6 4h10m-10 4h7M17 8h.01"/></svg>
                                </div>
                            @endif
                            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-700 shadow-sm">Berita</span>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <time class="text-xs font-semibold uppercase tracking-wide text-brand-600">
                                {{ $b->tanggal_terbit?->translatedFormat('j F Y') }}
                            </time>
                            <h3 class="mt-2 font-semibold text-slate-900 group-hover:text-brand-700">{{ $b->judul }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($b->isi), 140) }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-slate-500">Belum ada berita.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Pengumuman --}}
    <section id="pengumuman" class="py-20 md:py-28">
        <div class="mx-auto max-w-7xl px-4">
            <div class="reveal max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Kabar Terbaru</span>
                <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Pengumuman</h2>
                <p class="mt-3 text-slate-600">Informasi terbaru seputar periode usulan dan pelaksanaan.</p>
            </div>
            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @forelse ($announcements as $i => $a)
                    <article class="reveal group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl" data-d="{{ $i + 1 }}">
                        <div class="h-1.5 bg-gradient-to-r from-brand-600 via-accent-500 to-gold-400"></div>
                        <div class="flex flex-1 flex-col p-6">
                            <time class="text-xs font-semibold uppercase tracking-wide text-brand-600">
                                {{ $a->tanggal_terbit?->translatedFormat('j F Y') }}
                            </time>
                            <h3 class="mt-2 font-semibold text-slate-900 group-hover:text-brand-700">{{ $a->judul }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($a->isi), 170) }}</p>
                            @if ($a->lampiranUrl())
                                <a href="{{ $a->lampiranUrl() }}" target="_blank" rel="noopener"
                                   class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:underline">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14"/></svg>
                                    Unduh PDF
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="text-slate-500">Belum ada pengumuman.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative overflow-hidden bg-brand-950 text-white">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 grid-lines opacity-70"></div>
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-accent-500/30 blur-3xl animate-drift"></div>
            <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl animate-driftx"></div>
        </div>
        <div class="relative mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 py-16 text-center md:flex-row md:text-left">
            <div class="reveal">
                <h2 class="font-display text-2xl font-extrabold tracking-tight md:text-3xl">Siap mengajukan usulan?</h2>
                <p class="mt-2 text-brand-100/85">Masuk dengan akun institusi Anda untuk memulai.</p>
            </div>
            <a href="{{ url('/admin') }}"
               class="reveal inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-semibold text-brand-800 shadow-xl shadow-black/20 transition hover:-translate-y-0.5" data-d="1">
                Masuk ke Sistem
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </section>

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
                    <li><a href="#alur" class="transition hover:text-white">Alur Layanan</a></li>
                    <li><a href="#peran" class="transition hover:text-white">Peran Pengguna</a></li>
                    <li><a href="#berita" class="transition hover:text-white">Berita</a></li>
                    <li><a href="#pengumuman" class="transition hover:text-white">Pengumuman</a></li>
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

    <script>
        (function () {
            var header = document.getElementById('site-header');
            var onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 8); };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            var slider = document.getElementById('hero-slider');
            if (slider) {
                var slides = slider.querySelectorAll('.hero-slide');
                var dots = slider.querySelectorAll('[data-slide-dot]');
                var current = 0;
                var timer = null;

                var show = function (index) {
                    current = (index + slides.length) % slides.length;
                    slides.forEach(function (s, i) {
                        s.classList.toggle('opacity-100', i === current);
                        s.classList.toggle('z-10', i === current);
                        s.classList.toggle('opacity-0', i !== current);
                        s.classList.toggle('z-0', i !== current);
                    });
                    dots.forEach(function (d, i) {
                        d.classList.toggle('w-6', i === current);
                        d.classList.toggle('bg-white', i === current);
                        d.classList.toggle('w-2', i !== current);
                        d.classList.toggle('bg-white/40', i !== current);
                    });
                };

                var restart = function () {
                    if (timer) clearInterval(timer);
                    if (slides.length > 1) timer = setInterval(function () { show(current + 1); }, 6000);
                };

                if (slides.length > 1) {
                    dots.forEach(function (d, i) { d.addEventListener('click', function () { show(i); restart(); }); });

                    var prev = document.getElementById('hero-slider-prev');
                    var next = document.getElementById('hero-slider-next');
                    if (prev) prev.addEventListener('click', function () { show(current - 1); restart(); });
                    if (next) next.addEventListener('click', function () { show(current + 1); restart(); });

                    slider.addEventListener('mouseenter', function () { if (timer) clearInterval(timer); });
                    slider.addEventListener('mouseleave', restart);

                    restart();
                }
            }

            var btn = document.getElementById('menu-btn');
            var menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', function () { menu.classList.toggle('hidden'); });
                menu.querySelectorAll('a').forEach(function (a) {
                    a.addEventListener('click', function () { menu.classList.add('hidden'); });
                });
            }

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (!e.isIntersecting) return;
                    var el = e.target;
                    el.classList.add('in');
                    if (el.dataset.count !== undefined) {
                        var target = parseInt(el.dataset.count, 10) || 0, dur = 1100, t0 = performance.now();
                        var tick = function (now) {
                            var p = Math.min((now - t0) / dur, 1);
                            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
                            if (p < 1) requestAnimationFrame(tick);
                        };
                        requestAnimationFrame(tick);
                    }
                    io.unobserve(el);
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal, [data-count]').forEach(function (el) { io.observe(el); });
        })();
    </script>
</body>
</html>
