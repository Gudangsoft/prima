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
    | Pengumuman yang tampil di halaman publik & widget dasbor. Sunting di sini
    | (atau ganti dengan sumber dari database bila diperlukan nanti).
    */
    'announcements' => [
        [
            'tanggal' => '2 September 2026',
            'judul'   => 'Pembukaan Usulan Tahun Anggaran 2026',
            'isi'     => 'Pengusulan penelitian dan pengabdian untuk tahun anggaran 2026 telah dibuka. Pastikan skema yang dipilih sesuai dengan bidang Anda.',
        ],
        [
            'tanggal' => '20 Agustus 2026',
            'judul'   => 'Batas Unggah Laporan Kemajuan',
            'isi'     => 'Laporan kemajuan untuk usulan yang sedang berjalan paling lambat diunggah akhir bulan berjalan melalui menu Laporan Monev.',
        ],
        [
            'tanggal' => '5 Agustus 2026',
            'judul'   => 'Panduan Validasi Luaran Diperbarui',
            'isi'     => 'Ketentuan bukti luaran (publikasi, HKI, produk) telah disesuaikan. Unggah bukti pada menu Luaran di detail usulan.',
        ],
    ],
];
