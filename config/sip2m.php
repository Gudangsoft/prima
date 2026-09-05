<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Konfigurasi domain SIP2M
|--------------------------------------------------------------------------
| Nilai-nilai yang dipakai lintas modul: nama role, parameter OTP, dan
| batas ukuran unggahan. Dipusatkan di sini agar tidak tersebar sebagai
| "magic string" di banyak file.
*/

return [

    /*
    | Nama role Spatie. Jangan ubah string-nya setelah produksi karena
    | tersimpan di tabel `roles`. Assign banyak role ke satu user diperbolehkan
    | (mis. seorang staf bisa admin_lppm + pimpinan sekaligus).
    */
    'roles' => [
        'dosen' => 'dosen',
        'admin_lppm' => 'admin_lppm',
        'reviewer' => 'reviewer',
        'pimpinan' => 'pimpinan',
        'super_admin' => 'super_admin', // bypass penuh, untuk developer/operator sistem
    ],

    /*
    | Gerbang OTP yang wajib dilewati sesudah login sebelum masuk dashboard.
    */
    'otp' => [
        'enabled' => filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOL),
        'length' => (int) env('OTP_LENGTH', 6),
        'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 10),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
        'session_key' => 'otp.verified_for', // menyimpan id user yang OTP-nya sudah lolos di sesi ini
    ],

    /*
    | Batas unggahan berkas PDF (dalam kilobyte). Dipakai oleh form request
    | dan skema form Filament.
    */
    'uploads' => [
        'proposal_max_kb' => (int) env('UPLOAD_MAX_PROPOSAL_KB', 10240),
        'report_max_kb' => (int) env('UPLOAD_MAX_REPORT_KB', 10240),
        'output_max_kb' => (int) env('UPLOAD_MAX_OUTPUT_KB', 10240),
        'accepted_mimes' => ['pdf'],
    ],

    /*
    | Default branding situs. Nilai ini dipakai bila belum diubah lewat menu
    | Pengaturan > Pengaturan Web (yang menyimpan ke tabel `settings`).
    */
    'branding' => [
        'app_name' => env('APP_NAME', 'SIP2M'),
        'primary_color' => '#3B5BD9',
        'logo_path' => null,   // path di disk public; null = pakai images/logo-sip2m.svg
        'favicon_path' => null,
        'login_note' => 'Sistem Informasi Penelitian & Pengabdian — LPPM',
        'hero_title' => 'Kelola usulan penelitian & pengabdian dalam satu alur terpadu',
        'hero_subtitle' => 'Dari pengajuan usulan oleh dosen, persetujuan pimpinan, penilaian reviewer, penetapan pendanaan, hingga monitoring dan validasi luaran.',
    ],

    /*
    | Konten footer halaman publik (dapat diubah lewat Pengaturan Web).
    */
    'footer' => [
        'lembaga' => 'Lembaga Penelitian & Pengabdian kepada Masyarakat',
        'deskripsi' => 'Sistem Informasi Penelitian & Pengabdian kepada Masyarakat — lingkungan internal LPPM/LP2M institusi.',
        'alamat' => 'Gedung Rektorat',
        'email' => 'lppm@institusi.ac.id',
        'telepon' => '',
        'copyright' => 'LPPM',
    ],

    /*
    | Identitas lembaga (tampil di kartu "Profil Lembaga Penelitian" pada dasbor
    | dan di halaman publik). Sesuaikan dengan data institusi Anda.
    */
    'institution' => [
        // Identitas PT (sama untuk kedua kategori).
        'kode_pt' => env('INSTITUSI_KODE_PT', '000000'),
        'nama' => env('INSTITUSI_NAMA', 'Universitas Contoh'),
        'klaster' => env('INSTITUSI_KLASTER', 'Kelompok Pratama'),

        // Profil per kategori lembaga. Bila field kategori kosong, di-fallback
        // ke nilai kategori "penelitian" (lihat App\Support\Settings).
        'penelitian' => [
            'nama_lembaga' => env('INSTITUSI_LEMBAGA', 'Lembaga Penelitian'),
            'sk_pendirian' => env('INSTITUSI_SK_PENDIRIAN', '-'),
            'alamat' => env('INSTITUSI_ALAMAT', 'Gedung Rektorat'),
            'telepon' => env('INSTITUSI_TELEPON', '-'),
            'fax' => env('INSTITUSI_FAX', '-'),
            'email' => env('INSTITUSI_EMAIL', 'lp@institusi.ac.id'),
            'website' => env('INSTITUSI_WEBSITE', '-'),
            'jabatan_pimpinan' => env('INSTITUSI_JABATAN_PIMPINAN', 'Ketua Lembaga Penelitian'),
            'pimpinan_nama' => env('INSTITUSI_PIMPINAN_NAMA', ''),
            'pimpinan_nidn' => env('INSTITUSI_PIMPINAN_NIDN', ''),
        ],
        'pengabdian' => [
            'nama_lembaga' => env('INSTITUSI_LEMBAGA_PKM', ''),
            'sk_pendirian' => env('INSTITUSI_SK_PENDIRIAN_PKM', ''),
            'alamat' => env('INSTITUSI_ALAMAT_PKM', ''),
            'telepon' => env('INSTITUSI_TELEPON_PKM', ''),
            'fax' => env('INSTITUSI_FAX_PKM', ''),
            'email' => env('INSTITUSI_EMAIL_PKM', ''),
            'website' => env('INSTITUSI_WEBSITE_PKM', ''),
            'jabatan_pimpinan' => env('INSTITUSI_JABATAN_PIMPINAN_PKM', 'Ketua Lembaga Pengabdian kepada Masyarakat'),
            'pimpinan_nama' => env('INSTITUSI_PIMPINAN_NAMA_PKM', ''),
            'pimpinan_nidn' => env('INSTITUSI_PIMPINAN_NIDN_PKM', ''),
        ],
    ],

    /*
    | Pengumuman yang tampil di halaman publik & widget dasbor. Sunting di sini
    | (atau ganti dengan sumber dari database bila diperlukan nanti).
    */
    'announcements' => [
        [
            'tanggal' => '2 September 2026',
            'judul' => 'Pembukaan Usulan Tahun Anggaran 2026',
            'isi' => 'Pengusulan penelitian dan pengabdian untuk tahun anggaran 2026 telah dibuka. Pastikan skema yang dipilih sesuai dengan bidang Anda.',
        ],
        [
            'tanggal' => '20 Agustus 2026',
            'judul' => 'Batas Unggah Laporan Kemajuan',
            'isi' => 'Laporan kemajuan untuk usulan yang sedang berjalan paling lambat diunggah akhir bulan berjalan melalui menu Laporan Monev.',
        ],
        [
            'tanggal' => '5 Agustus 2026',
            'judul' => 'Panduan Validasi Luaran Diperbarui',
            'isi' => 'Ketentuan bukti luaran (publikasi, HKI, produk) telah disesuaikan. Unggah bukti pada menu Luaran di detail usulan.',
        ],
    ],
];
