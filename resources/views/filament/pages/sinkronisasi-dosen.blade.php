<x-filament-panels::page>
    <style>
        .sd-wrap { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .sd-heading { margin:0 0 16px; font-size:15px; font-weight:800; color:#1f2937; letter-spacing:.02em; }
        .sd-filters { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:flex-end; gap:10px; margin-bottom:16px; }
        .sd-filters input, .sd-filters select {
            padding:9px 12px; font-size:13px; color:#1f2937;
            background:#fff; border:1px solid #d1d5db; border-radius:8px;
        }
        .sd-filters input { min-width:220px; }
        .sd-btn-cari {
            padding:9px 18px; font-size:13px; font-weight:600; color:#fff;
            background:#3b5bd9; border:0; border-radius:8px; cursor:pointer;
        }
        .sd-btn-cari:hover { background:#2f49b0; }
        .sd-table-scroll { overflow-x:auto; }
        .sd-table { width:100%; border-collapse:collapse; font-size:13px; }
        .sd-table th, .sd-table td { border:1px solid #eef0f3; padding:12px 14px; text-align:left; vertical-align:top; }
        .sd-table thead th { background:#f8fafc; color:#6b7280; font-weight:700; white-space:nowrap; }
        .sd-table tbody tr:nth-child(even) { background:#fbfcfd; }
        .sd-kv { display:grid; grid-template-columns:auto 1fr; column-gap:8px; row-gap:2px; }
        .sd-kv dt { color:#9ca3af; font-size:11px; text-transform:uppercase; letter-spacing:.03em; }
        .sd-kv dd { margin:0 0 6px; font-weight:600; color:#1f2937; }
        .sd-empty { padding:22px; text-align:center; color:#9ca3af; }
        .sd-edit {
            display:inline-block; padding:6px 14px; font-size:12px; font-weight:600;
            color:#fff; background:#3b5bd9; border-radius:6px; text-decoration:none; white-space:nowrap;
        }
        .sd-edit:hover { background:#2f49b0; }
    </style>

    <div class="sd-wrap" style="margin-bottom:20px;">
        <h2 class="sd-heading">Daftar Dosen</h2>

        <div class="sd-filters">
            <input type="text" wire:model.live.debounce.400ms="cari" placeholder="Cari Data Dosen">
            <select wire:model.live="berdasarkan">
                <option value="nama">Berdasarkan Nama</option>
                <option value="nidn">Berdasarkan NIDN</option>
            </select>
            <select wire:model.live="perHalaman">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>

        <div class="sd-table-scroll">
            <table class="sd-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Institusi</th>
                        <th>Personal</th>
                        <th>Identitas</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->dosen as $i => $d)
                        <tr>
                            <td>{{ $this->dosen->firstItem() + $i }}</td>
                            <td>
                                <dl class="sd-kv">
                                    <dt>PT</dt><dd>{{ $this->institusi }}</dd>
                                    <dt>Prodi</dt><dd>{{ $d->programStudi?->nama ?: '—' }}</dd>
                                    <dt>Pendidikan</dt><dd>{{ $d->pendidikan_terakhir ?: '—' }}</dd>
                                </dl>
                            </td>
                            <td>
                                <dl class="sd-kv">
                                    <dt>NIDN</dt><dd>{{ $d->nidn ?: '—' }}</dd>
                                    <dt>Nama</dt><dd>{{ $d->name }}</dd>
                                </dl>
                            </td>
                            <td>
                                <dl class="sd-kv">
                                    <dt>Jabatan Fungsional</dt><dd>{{ $d->jabatan ?: '—' }}</dd>
                                    <dt>No. HP</dt><dd>{{ $d->phone_number ?: '—' }}</dd>
                                    <dt>Surel</dt><dd>{{ $d->email }}</dd>
                                </dl>
                            </td>
                            <td><a class="sd-edit" href="{{ $this->editUrl($d) }}" wire:navigate>Edit</a></td>
                        </tr>
                    @empty
                        <tr><td class="sd-empty" colspan="5">Belum ada data dosen. Impor lewat tombol di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $this->dosen->links() }}
        </div>
    </div>

    <x-filament::section icon="heroicon-o-information-circle" heading="Cara pakai" collapsible collapsed>
        <ol style="margin:0;padding-left:18px;line-height:1.9;font-size:13px;color:#374151;">
            <li>Unduh <strong>template CSV</strong> (tombol di atas) atau ekspor data dosen dari SIAKAD/PDDIKTI.</li>
            <li>Kolom: <code>nidn</code>, <code>name</code>, <code>gelar_depan</code>, <code>gelar_belakang</code>,
                <code>sinta_id</code>, <code>pendidikan_terakhir</code>,
                <code>sinta_score_overall_v2</code>, <code>sinta_score_3yr_v2</code>,
                <code>sinta_score_overall_v3</code>, <code>sinta_score_3yr_v3</code>,
                <code>phone_number</code>, <code>jabatan</code>, <code>kompetensi</code>, <code>kode_prodi</code>.
                Hanya <code>nidn</code> dan <code>name</code> yang wajib — email tidak diminta lagi,
                akun baru otomatis diberi email placeholder karena dosen login memakai <strong>NIDN</strong>.
                <code>gelar_depan</code>/<code>gelar_belakang</code> otomatis digabung ke nama lengkap.</li>
            <li>Klik <strong>Impor CSV Dosen</strong>, unggah berkas, dan petakan kolom bila perlu.</li>
            <li>Baris dicocokkan berdasarkan <strong>NIDN</strong>: akun yang sudah ada
                <em>diperbarui</em>, yang belum ada <em>dibuat</em> dengan peran <strong>dosen</strong>
                dan kata sandi acak (dosen login pakai NIDN, ganti kata sandi lewat profil setelah masuk pertama kali).</li>
            <li><code>kode_prodi</code> ditautkan ke Program Studi bila kodenya sudah terdaftar
                (lihat menu <strong>Sinkronisasi Prodi</strong>).</li>
            <li>Aplikasi ini tidak terhubung ke PDDIKTI nasional (beda dari BIMA), jadi belum ada sinkronisasi
                otomatis per-dosen — perbarui data lewat impor ulang CSV atau tombol <strong>Edit</strong>.</li>
        </ol>
    </x-filament::section>

    <x-filament::section icon="heroicon-o-clock" heading="Riwayat impor terakhir" collapsible collapsed>
        @php($recent = \Filament\Actions\Imports\Models\Import::query()->latest()->limit(10)->get())
        @if ($recent->isEmpty())
            <p style="font-size:13px;color:#6b7280;">Belum ada impor.</p>
        @else
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#9ca3af;">
                        <th style="padding:6px 8px;">Waktu</th>
                        <th style="padding:6px 8px;">Berkas</th>
                        <th style="padding:6px 8px;text-align:right;">Berhasil</th>
                        <th style="padding:6px 8px;text-align:right;">Total baris</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $imp)
                        <tr style="border-top:1px solid #f1f2f4;">
                            <td style="padding:6px 8px;">{{ $imp->created_at?->format('d M Y H:i') }}</td>
                            <td style="padding:6px 8px;">{{ $imp->file_name }}</td>
                            <td style="padding:6px 8px;text-align:right;font-weight:700;">{{ number_format($imp->successful_rows) }}</td>
                            <td style="padding:6px 8px;text-align:right;">{{ number_format($imp->total_rows) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
