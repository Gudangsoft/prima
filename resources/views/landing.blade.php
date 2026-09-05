@php
    $alur = [
        ['draft', 'Usulan Baru', 'Dosen menyusun usulan: pilih skema, isi substansi, unggah proposal PDF.'],
        ['submitted', 'Pengajuan', 'Usulan dikirim ke LPPM untuk diperiksa kelengkapannya.'],
        ['approved_lppm', 'Persetujuan Institusi', 'Admin LPPM / Pimpinan menyetujui usulan yang layak lanjut.'],
        ['under_review', 'Penilaian Reviewer', 'Reviewer yang ditugaskan memberi skor dan rekomendasi.'],
        ['funded', 'Penetapan Pendanaan', 'LPPM menetapkan status pendanaan dan nomor SK.'],
        ['in_progress', 'Pelaksanaan & Monev', 'Kegiatan berjalan; dosen mengunggah laporan kemajuan.'],
        ['reported', 'Pelaporan Akhir', 'Laporan akhir diserahkan dan diverifikasi.'],
        ['output_validated', 'Validasi Luaran', 'Bukti luaran (publikasi/HKI/produk) divalidasi LPPM.'],
    ];

    $peran = [
        ['Dosen', 'Mengajukan usulan, memantau status, mengunggah laporan & bukti luaran.', 'M12 14c3.31 0 6-2.69 6-6s-2.69-6-6-6-6 2.69-6 6 2.69 6 6 6Zm0 2c-4 0-12 2-12 6v2h24v-2c0-4-8-6-12-6Z'],
        ['Admin LPPM', 'Mengelola skema & periode, menyetujui usulan, menugaskan reviewer, menetapkan pendanaan.', 'M12 2 3 6v6c0 5.25 3.75 9.75 9 11 5.25-1.25 9-5.75 9-11V6l-9-4Zm0 4 5 2.2V12c0 3.7-2.4 6.9-5 7.8-2.6-.9-5-4.1-5-7.8V8.2L12 6Z'],
        ['Reviewer', 'Menilai usulan yang ditugaskan, memberi skor dan rekomendasi kelayakan.', 'M4 4h16v2H4V4Zm0 5h16v2H4V9Zm0 5h10v2H4v-2Zm12.5 6L13 16.5l1.5-1.5 2 2 4-4L22 14.5 16.5 20Z'],
        ['Pimpinan / Ketua LPPM', 'Pengawasan menyeluruh: persetujuan final institusi dan rekap capaian.', 'M3 13h2v7H3v-7Zm4-4h2v11H7V9Zm4-6h2v17h-2V3Zm4 9h2v8h-2v-8Zm4-4h2v12h-2V8Z'],
    ];

    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIP2M &mdash; Sistem Informasi Penelitian &amp; Pengabdian</title>
    <meta name="description" content="Portal pengelolaan usulan penelitian dan pengabdian kepada masyarakat untuk lingkungan LPPM/LP2M.">
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:'#eef5fc',100:'#d6e6f7',200:'#aecdef',300:'#7fb0e4',
                            400:'#4d8ed4',500:'#2b73bd',600:'#1B5E9C',700:'#164e82',
                            800:'#123f69',900:'#0f3454',
                        },
                        gold: { 400:'#F4B740', 500:'#e0a52c' },
                    },
                    fontFamily: { sans: ['Inter','ui-sans-serif','system-ui','Segoe UI','Roboto','sans-serif'] },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak]{display:none}
        .hero-grid{background-image:linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);background-size:44px 44px}
    </style>
</head>
<body class="bg-slate-50 text-slate-700 antialiased">

    {{-- Top strip --}}
    <div class="bg-brand-800 text-brand-100 text-xs">
        <div class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between">
            <span class="hidden sm:block">Lembaga Penelitian &amp; Pengabdian kepada Masyarakat</span>
            <span class="sm:hidden">LPPM</span>
            <span>Tahun Anggaran {{ $tahunAktif }}</span>
        </div>
    </div>

    {{-- Header --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="mx-auto max-w-7xl px-4 h-16 flex items-center justify-between gap-4">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sip2m.svg') }}" alt="SIP2M" class="h-9 w-9">
                <span class="leading-tight">
                    <span class="block font-extrabold text-slate-900 tracking-tight text-lg">SIP2M</span>
                    <span class="block text-[11px] text-slate-500">Sistem Informasi Penelitian &amp; Pengabdian</span>
                </span>
            </a>
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-600">
                <a href="#beranda" class="hover:text-brand-600">Beranda</a>
                <a href="#alur" class="hover:text-brand-600">Alur Layanan</a>
                <a href="#peran" class="hover:text-brand-600">Peran Pengguna</a>
                <a href="#pengumuman" class="hover:text-brand-600">Pengumuman</a>
            </nav>
            <a href="{{ url('/admin') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                Masuk ke Sistem
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section id="beranda" class="relative overflow-hidden bg-brand-700 text-white">
        <div class="absolute inset-0 hero-grid opacity-60"></div>
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-500/40 blur-3xl"></div>
        <div class="absolute -left-32 bottom-0 h-80 w-80 rounded-full bg-gold-400/20 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 md:py-28">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium ring-1 ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                    Platform internal LPPM &mdash; terinspirasi alur kerja BIMA
                </span>
                <h1 class="mt-5 text-4xl md:text-5xl font-extrabold tracking-tight leading-[1.1]">
                    Kelola usulan penelitian &amp; pengabdian dalam satu alur terpadu
                </h1>
                <p class="mt-5 text-lg text-brand-100/90 max-w-2xl">
                    Dari pengajuan usulan oleh dosen, persetujuan pimpinan, penilaian reviewer,
                    penetapan pendanaan, hingga monitoring dan validasi luaran &mdash; semuanya
                    tercatat dan terpantau.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ url('/admin') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand-700 shadow hover:bg-brand-50 transition">
                        Masuk / Ajukan Usulan
                    </a>
                    <a href="#alur"
                       class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/25 hover:bg-white/15 transition">
                        Pelajari Alur Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats band --}}
    <section class="relative -mt-10 z-10">
        <div class="mx-auto max-w-7xl px-4">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-px overflow-hidden rounded-2xl bg-slate-200 shadow-lg ring-1 ring-slate-200">
                @foreach ([
                    ['Total Usulan', number_format($stats['usulan'], 0, ',', '.')],
                    ['Usulan Didanai', number_format($stats['didanai'], 0, ',', '.')],
                    ['Dosen Terdaftar', number_format($stats['dosen'], 0, ',', '.')],
                    ['Reviewer', number_format($stats['reviewer'], 0, ',', '.')],
                    ['Skema Aktif', number_format($stats['skema_aktif'], 0, ',', '.')],
                    ['Dana Tersalur', $rp($stats['dana'])],
                ] as [$label, $value])
                    <div class="bg-white px-5 py-6 text-center">
                        <div class="text-2xl font-extrabold text-brand-700 tracking-tight break-words">{{ $value }}</div>
                        <div class="mt-1 text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Alur --}}
    <section id="alur" class="py-20">
        <div class="mx-auto max-w-7xl px-4">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Alur Layanan</h2>
                <p class="mt-3 text-slate-600">Delapan tahap siklus usulan, dari draf hingga luaran tervalidasi. Setiap perpindahan status tercatat otomatis pada riwayat usulan.</p>
            </div>
            <ol class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($alur as $i => [$key, $judul, $desc])
                    <li class="relative rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">{{ $i + 1 }}</span>
                            <h3 class="font-semibold text-slate-900 leading-tight">{{ $judul }}</h3>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">{{ $desc }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Peran --}}
    <section id="peran" class="py-20 bg-white border-y border-slate-200">
        <div class="mx-auto max-w-7xl px-4">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Peran Pengguna</h2>
            <p class="mt-3 max-w-2xl text-slate-600">Akses dan wewenang dibatasi per peran. Satu akun dapat memegang lebih dari satu peran.</p>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($peran as [$nama, $desc, $path])
                    <div class="rounded-xl border border-slate-200 p-6 hover:border-brand-300 hover:shadow-md transition">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $nama }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pengumuman --}}
    <section id="pengumuman" class="py-20">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Pengumuman</h2>
                    <p class="mt-3 text-slate-600">Informasi terbaru seputar periode usulan dan pelaksanaan.</p>
                </div>
            </div>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach (config('sip2m.announcements', []) as $a)
                    <article class="flex flex-col rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <time class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ $a['tanggal'] }}</time>
                        <h3 class="mt-2 font-semibold text-slate-900">{{ $a['judul'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600 flex-1">{{ $a['isi'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-brand-700">
        <div class="mx-auto max-w-7xl px-4 py-14 flex flex-col md:flex-row items-center justify-between gap-6 text-white">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight">Siap mengajukan usulan?</h2>
                <p class="mt-2 text-brand-100/90">Masuk dengan akun institusi Anda untuk memulai.</p>
            </div>
            <a href="{{ url('/admin') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow hover:bg-brand-50 transition">
                Masuk ke Sistem
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-400">
        <div class="mx-auto max-w-7xl px-4 py-14 grid gap-10 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-sip2m.svg') }}" alt="SIP2M" class="h-9 w-9">
                    <span class="font-extrabold text-white text-lg">SIP2M</span>
                </div>
                <p class="mt-4 text-sm max-w-xs">Sistem Informasi Penelitian &amp; Pengabdian kepada Masyarakat &mdash; lingkungan internal LPPM/LP2M institusi.</p>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white">Navigasi</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="#alur" class="hover:text-white">Alur Layanan</a></li>
                    <li><a href="#peran" class="hover:text-white">Peran Pengguna</a></li>
                    <li><a href="#pengumuman" class="hover:text-white">Pengumuman</a></li>
                    <li><a href="{{ url('/admin') }}" class="hover:text-white">Masuk ke Sistem</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white">Kontak</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>LPPM &mdash; Gedung Rektorat</li>
                    <li>lppm@institusi.ac.id</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800">
            <div class="mx-auto max-w-7xl px-4 py-5 text-xs flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>&copy; {{ date('Y') }} LPPM. Seluruh hak cipta dilindungi.</span>
                <span>Dibangun dengan Laravel &amp; Filament.</span>
            </div>
        </div>
    </footer>
</body>
</html>
