<x-filament-panels::page>
    <style>
        .pd-tabs { display:flex; gap:6px; border-bottom:1px solid #e5e7eb; margin-bottom:20px; }
        .pd-tab { padding:9px 18px; border-radius:8px 8px 0 0; font-size:13px; font-weight:600; color:#6b7280; border:1px solid transparent; border-bottom:none; cursor:pointer; background:none; }
        .pd-tab:hover { color:#2f43b8; background:#f3f4f6; }
        .pd-tab.pd-active { color:#fff; background:#2f43b8; }
        .pd-card { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:22px 24px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .pd-card + .pd-card { margin-top:16px; }
        .pd-card h3 { display:flex; align-items:center; gap:8px; margin:0 0 12px; font-size:15px; font-weight:800; color:#1f2937; }
        .pd-card h3 .pd-num { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:9999px; background:#e0e7ff; color:#3730a3; font-size:12px; font-weight:800; flex-shrink:0; }
        .pd-card ol, .pd-card ul { margin:0; padding-left:20px; }
        .pd-card li { font-size:13.5px; line-height:1.7; color:#374151; }
        .pd-card li + li { margin-top:4px; }
        .pd-card code { background:#f1f5f9; padding:1px 6px; border-radius:5px; font-size:12.5px; color:#334155; }
        .pd-dl { display:inline-flex; align-items:center; gap:8px; margin-bottom:16px; padding:9px 16px; border-radius:8px; background:#2f43b8; color:#fff; font-size:13px; font-weight:600; text-decoration:none; }
        .pd-dl:hover { background:#26379c; }
        .pd-hint { font-size:12.5px; color:#6b7280; margin-bottom:16px; }
        [x-cloak] { display:none !important; }
    </style>

    <div x-data="{ tab: 'pengguna' }">
        <div class="pd-tabs">
            <button type="button" class="pd-tab" :class="{ 'pd-active': tab === 'pengguna' }" @click="tab = 'pengguna'">
                Panduan Pengguna
            </button>
            @if ($bolehLihatAdmin)
                <button type="button" class="pd-tab" :class="{ 'pd-active': tab === 'admin' }" @click="tab = 'admin'">
                    Panduan Admin LPPM
                </button>
            @endif
        </div>

        {{-- ============================ PENGGUNA ============================ --}}
        <div x-show="tab === 'pengguna'">
            @if ($pdfPengguna)
                <a href="{{ $pdfPengguna }}" target="_blank" class="pd-dl">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Unduh Panduan Lengkap (PDF)
                </a>
            @else
                <div class="pd-hint">Panduan ringkas di bawah. Versi PDF lengkap belum diunggah admin.</div>
            @endif

            <div class="pd-card">
                <h3><span class="pd-num">1</span> Masuk &amp; Lengkapi Profil</h3>
                <ol>
                    <li>Login memakai <strong>email / NIDN / NUPTK</strong> dan kata sandi. Bila diminta OTP, lengkapi Nomor HP dulu.</li>
                    <li>Klik avatar di pojok kanan atas → <strong>Profil Saya</strong>. Lengkapi NIDN/NUPTK, jabatan fungsional, kompetensi, serta data Scopus/Web of Science.</li>
                    <li>Jika akun Anda punya lebih dari satu peran, gunakan badge peran di samping ikon lonceng untuk berpindah tampilan.</li>
                </ol>
            </div>

            <div class="pd-card">
                <h3><span class="pd-num">2</span> Mengajukan Usulan Baru</h3>
                <ol>
                    <li>Pada bar menu atas, buka <strong>Penelitian</strong> atau <strong>Pengabdian</strong>, lalu pilih <strong>nama skema</strong> yang dituju.</li>
                    <li>Halaman skema menampilkan daftar usulan Anda pada skema itu, ketentuan <strong>biaya</strong>, dan <strong>target luaran</strong> (wajib &amp; tambahan). Klik <strong>Info Eligibilitas</strong> untuk mengecek skema yang boleh diajukan tahun ini.</li>
                    <li>Klik <strong>Ajukan Usulan Baru</strong>, lalu isi wizard: Identitas Usulan, Anggota Tim, Substansi &amp; Luaran, RAB, Bidang Strategis, Mitra.</li>
                    <li>Unggah berkas proposal, lalu simpan. Usulan tersimpan sebagai <strong>Draf</strong>.</li>
                </ol>
            </div>

            <div class="pd-card">
                <h3><span class="pd-num">3</span> Anggota Tim &amp; Kirim Usulan</h3>
                <ol>
                    <li>Anggota berjenis <strong>dosen</strong> harus menyetujui undangan lebih dulu — mereka melihatnya di kartu <strong>Undangan Tim</strong> pada dashboard.</li>
                    <li>Setelah semua anggota dosen menyetujui dan berkas proposal terunggah, buka detail usulan lalu klik <strong>Kirim Usulan</strong>.</li>
                    <li>Setelah dikirim, usulan tidak bisa diedit lagi sampai ada keputusan LPPM.</li>
                </ol>
            </div>

            <div class="pd-card">
                <h3><span class="pd-num">4</span> Memantau Status</h3>
                <ul>
                    <li>Dashboard menampilkan ringkasan (Total Usulan, Usulan Didanai, Sedang Berjalan) dan <strong>Riwayat Usulan</strong>.</li>
                    <li>Buka <strong>Kelola Usulan</strong> dari menu Penelitian/Pengabdian untuk melihat kolom <strong>Status</strong>, <strong>Komentar LPPM</strong>, dan <strong>Komentar Reviewer</strong>.</li>
                    <li>Alur status: Draf → Diajukan → Disetujui LPPM → Dalam Penilaian → Didanai → Pelaksanaan → Laporan Masuk → Luaran Tervalidasi.</li>
                </ul>
            </div>

            <div class="pd-card">
                <h3><span class="pd-num">5</span> Setelah Usulan Didanai</h3>
                <ul>
                    <li>Pada halaman detail usulan tersedia tab: <strong>Catatan Harian</strong>, <strong>Laporan Kemajuan</strong>, <strong>Laporan Akhir</strong>, dan <strong>Luaran</strong>.</li>
                    <li>Isi Catatan Harian secara berkala; unggah Laporan Kemajuan lalu Laporan Akhir sesuai jadwal; catat capaian luaran hingga divalidasi.</li>
                </ul>
            </div>
        </div>

        {{-- ============================ ADMIN ============================ --}}
        @if ($bolehLihatAdmin)
            <div x-show="tab === 'admin'" x-cloak>
                @if ($pdfAdmin)
                    <a href="{{ $pdfAdmin }}" target="_blank" class="pd-dl">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        Unduh Panduan Admin Lengkap (PDF)
                    </a>
                @else
                    <div class="pd-hint">Panduan ringkas di bawah. Versi PDF lengkap belum diunggah.</div>
                @endif

                <div class="pd-card">
                    <h3><span class="pd-num">1</span> Menyiapkan Data Pendukung</h3>
                    <ul>
                        <li><strong>Data Pendukung → Skema Usulan</strong>: buat skema (nama, kategori, biaya minimal/maksimal, periode buka–tutup, target luaran wajib &amp; tambahan, template usulan). Skema hanya muncul ke dosen bila <em>Aktif</em> dan dalam periodenya.</li>
                        <li><strong>Data Pendukung → Sinkronisasi Dosen</strong>: impor akun dosen dari berkas <em>Export Author</em> SINTA (CSV). Akun baru otomatis berperan "Dosen".</li>
                        <li><strong>Data Pendukung → Program Studi</strong> &amp; <strong>Cari Akun</strong>: kelola prodi dan akun pengguna. Untuk menjadikan seorang dosen juga sebagai reviewer, buka akunnya lalu tambahkan peran <strong>Reviewer</strong>.</li>
                    </ul>
                </div>

                <div class="pd-card">
                    <h3><span class="pd-num">2</span> Mengelola Reviewer</h3>
                    <ul>
                        <li><strong>Pengelolaan Reviewer → Daftar Reviewer</strong>: daftar semua akun berperan Reviewer beserta beban penilaiannya. Tombol <em>Tambah Reviewer</em> membuat akun reviewer baru.</li>
                        <li><strong>Pengelolaan Reviewer → Plotting Reviewer</strong>: tugaskan reviewer ke tiap usulan (saring per Tahun, Tahapan, dan Kegiatan).</li>
                    </ul>
                </div>

                <div class="pd-card">
                    <h3><span class="pd-num">3</span> Alur Penilaian Usulan</h3>
                    <ol>
                        <li>Usulan masuk berstatus <strong>Diajukan</strong>. Buka detailnya, lalu <strong>Setujui</strong> atau <strong>Tolak</strong> di tingkat LPPM (tolak wajib disertai catatan).</li>
                        <li>Setelah disetujui, <strong>Tugaskan Reviewer</strong> (via Plotting Reviewer). Status berpindah ke <strong>Dalam Penilaian</strong>.</li>
                        <li>Reviewer mengisi skor &amp; rekomendasi. Setelah penilaian masuk, LPPM melakukan <strong>Tetapkan Pendanaan</strong> (Didanai / Didanai Sebagian / Tidak Didanai, beserta nominal &amp; nomor SK).</li>
                    </ol>
                </div>

                <div class="pd-card">
                    <h3><span class="pd-num">4</span> Monitoring Pelaksanaan</h3>
                    <ul>
                        <li><strong>Monitoring → Pelaksanaan</strong>: pantau progres usulan yang sedang berjalan (Laporan Kemajuan/Akhir, persentase capaian).</li>
                        <li><strong>Monitoring → Catatan Harian</strong>: tinjau catatan harian yang diisi dosen.</li>
                        <li><strong>Monitoring Usulan</strong> (grup Monitoring): rekap seluruh usulan lintas skema &amp; tahun, bisa diekspor ke Excel.</li>
                    </ul>
                </div>

                <div class="pd-card">
                    <h3><span class="pd-num">5</span> Pengaturan (Super Admin)</h3>
                    <ul>
                        <li><strong>Pengaturan → Pengaturan Web</strong>: nama aplikasi, warna, logo/favicon, konten hero &amp; footer halaman depan, identitas PT, profil Lembaga Penelitian &amp; Pengabdian, serta <strong>unggah PDF Buku Panduan</strong> ini.</li>
                        <li><strong>Pengaturan → Profil Lembaga</strong>: data lembaga yang tampil pada dokumen &amp; kop.</li>
                        <li>Akun dengan lebih dari satu peran dapat berpindah "lensa" tampilan lewat badge peran di samping ikon lonceng — ini hanya mengubah tampilan menu, bukan hak akses data.</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
