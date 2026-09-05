# SIP2M — Sistem Informasi Penelitian & Pengabdian

Sistem informasi manajemen proposal penelitian dan pengabdian kepada masyarakat
untuk LPPM/LP2M satu institusi. Alur kerja terinspirasi platform BIMA
Kemdiktisaintek: pengajuan usulan → persetujuan institusi → penilaian reviewer →
penetapan pendanaan → pelaksanaan & monev → pelaporan akhir → validasi luaran.

## Tech stack

| Komponen            | Pilihan                                                        |
| ------------------- | ------------------------------------------------------------- |
| Framework           | Laravel 12 (PHP 8.3+)                                        |
| Admin/UI            | Filament 3 (panel tunggal untuk semua peran) + Livewire 3    |
| Database            | MySQL 8                                                       |
| Role & permission   | `spatie/laravel-permission`                                  |
| Autentikasi         | Auth bawaan Filament + gerbang OTP email (middleware custom) |
| Queue               | driver `database` (siap di-upgrade ke Redis)                 |
| Storage berkas      | disk `local` (siap diarahkan ke S3-compatible)              |
| State machine usulan| enum PHP `ProposalStatus` + Model Observer pencatat riwayat  |

## Keputusan arsitektur

- **Panel tunggal Filament** di `/admin` untuk dosen, reviewer, admin LPPM, dan
  pimpinan. Visibilitas menu & aksi dibatasi lewat Policy + permission Spatie.
- **Gerbang OTP**: setelah login, `EnsureOtpVerified` memaksa user melengkapi
  nomor HP (bila kosong) lalu memasukkan kode OTP yang dikirim ke email sebelum
  dashboard bisa diakses. OTP wajib diulang setiap login baru.
- **Persetujuan institusi = gerbang tunggal**: status `approved_lppm` bisa
  diberikan oleh Admin LPPM **atau** Pimpinan. Empat role tetap dipisah di
  Spatie; satu user boleh memegang lebih dari satu role.
- **Validasi form**: untuk alur di dalam panel, aturan validasi ditulis di skema
  form Filament (menggantikan `FormRequest`). `FormRequest` dipakai bila ada
  controller HTTP sungguhan (mis. endpoint unduh berkas / API pada fase lanjut).

## Prasyarat

- PHP 8.3+ dengan ekstensi: `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`,
  `curl`, `openssl`, `bcmath`, `fileinfo`
- Composer 2
- Node 20+ & npm
- MySQL 8 (mis. lewat Laragon)

## Setup awal

```bash
cp .env.example .env
composer install
npm install

php artisan key:generate

# Buat database (sesuaikan kredensial di .env)
#   CREATE DATABASE sip2m CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

php artisan migrate --seed
npm run build   # atau: npm run dev
php artisan storage:link
```

Jalankan aplikasi:

```bash
php artisan serve
php artisan queue:work        # memproses pengiriman email OTP & notifikasi
```

Buka `http://127.0.0.1:8000/admin`.

### Mail di lingkungan dev

`MAIL_MAILER=log` — email OTP tidak benar-benar terkirim, tetapi isinya
(termasuk kode 6 digit) tercatat di `storage/logs/laravel.log`.

## Akun contoh (hasil seeder)

Semua kata sandi: **`password`**

| Email                    | Peran        | Catatan                                            |
| ------------------------ | ------------ | ------------------------------------------------- |
| `superadmin@sip2m.test`  | super_admin  | bypass semua policy (operator sistem)             |
| `admin@sip2m.test`       | admin_lppm   | kelola skema, approval, penugasan, pendanaan      |
| `pimpinan@sip2m.test`    | pimpinan     | approval final institusi, dashboard rekap         |
| `reviewer@sip2m.test`    | reviewer     | menilai usulan yang ditugaskan                    |
| `dosen@sip2m.test`       | dosen        | nomor HP sengaja kosong → mendemokan "Lengkapi Profil" |

## Konfigurasi domain

Lihat `config/sip2m.php` (nama role, parameter OTP, batas unggahan) dan variabel
`.env` terkait (`OTP_*`, `UPLOAD_MAX_*`).

## Testing

```bash
php artisan test
```

Suite memakai database MySQL terpisah `sip2m_test` (lihat `phpunit.xml`) karena
`pdo_sqlite` tidak tersedia di lingkungan pengembangan ini. Buat dulu:

```sql
CREATE DATABASE sip2m_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Status modul (urutan MVP)

- [x] 1. Auth & manajemen role — login + gerbang OTP email, RBAC Spatie, CRUD Pengguna
- [x] 2. Manajemen skema usulan (Admin LPPM) — CRUD + toggle aktif
- [x] 3. Pengajuan usulan (Dosen) — wizard 3 langkah, simpan draf, kirim final, riwayat status
- [x] 4. Approval institusi (Admin LPPM / Pimpinan) — aksi Setujui/Tolak (catatan wajib saat tolak)
- [x] 5. Penugasan & penilaian reviewer — relation manager "Penilaian Reviewer"
- [x] 6. Penetapan pendanaan — aksi Tetapkan Pendanaan + `funding_decisions`
- [x] 7. Monitoring & pelaporan — relation manager "Laporan Monev" (kemajuan/akhir)
- [x] 8. Validasi luaran — relation manager "Luaran" + aksi Validasi
- [x] 9. Notifikasi email otomatis — event `ProposalStatusChanged` + notifikasi ter-queue
- [x] 10. Dashboard & laporan rekap — widget stats + 3 chart (status / skema / tahun)

Semua modul di atas punya cakupan test di `tests/` (`php artisan test`).

## Peta alur & state machine

```
draft ─▶ submitted ─▶ approved_lppm ─▶ under_review ─▶ funded ─▶ in_progress ─▶ reported ─▶ output_validated
                │            │              │
                └──▶ draft   └──▶ rejected ◀┘         reported ──▶ in_progress (revisi laporan)
                └──▶ rejected
```

- Definisi transisi: [app/Enums/ProposalStatus.php](app/Enums/ProposalStatus.php) (`map()` / `canTransitionTo()`).
- Satu-satunya pintu perubahan status: [ChangeProposalStatus](app/Actions/Proposal/ChangeProposalStatus.php);
  setiap transisi otomatis dicatat ke `proposal_status_histories` oleh
  [ProposalObserver](app/Observers/ProposalObserver.php) dan memancarkan event
  `ProposalStatusChanged`.
- Aksi domain per tahap ada di [app/Actions/Proposal/](app/Actions/Proposal/) — dipanggil
  dari row action tabel maupun header action halaman detail usulan.

## Berkas privat

Semua unggahan (proposal, laporan, luaran, SK) disimpan di disk `local`
(`storage/app/private`). Diakses lewat route ber-otorisasi:
`GET /berkas/{usulan|laporan|luaran|sk}/{id}` → [FileDownloadController](app/Http/Controllers/FileDownloadController.php),
yang mengecek `ProposalPolicy` sebelum men-stream file.

## Tampilan

- **Halaman publik** `/` — portal bergaya BIMA (identitas institusi sendiri "SIP2M",
  tanpa logo/segel Kemdiktisaintek): hero, angka statistik langsung dari DB, 8 tahap
  alur layanan, peran pengguna, pengumuman, footer.
  [LandingController](app/Http/Controllers/LandingController.php) ·
  [resources/views/landing.blade.php](resources/views/landing.blade.php) ·
  memakai Tailwind Play CDN (untuk offline: `npm run build` lalu ganti ke `@vite`).
- **Panel** — brand "SIP2M" + logo/favicon SVG di `public/images/`, warna primer
  biru `#1B5E9C`, judul dasbor "Dasbor" dengan sub-judul, catatan institusi di
  halaman login. Diatur di [AdminPanelProvider](app/Providers/Filament/AdminPanelProvider.php).
- **Dasbor admin** — kartu ringkasan (Total Usulan, Dalam Proses, Usulan Didanai,
  Dana Tersalur, Luaran Tervalidasi, Ditolak), 3 chart rekap, tabel "Usulan Terbaru",
  dan panel "Pengumuman" (sumber `config('sip2m.announcements')`). Dosen melihat
  ringkasan usulannya sendiri.

## Data contoh

`DemoProposalSeeder` membuat 15 usulan menyebar di semua status (beserta approval,
review, pendanaan, laporan, dan luaran yang konsisten) sehingga dashboard langsung
berisi. Seeder ini dilewati bila tabel `proposals` sudah terisi.
