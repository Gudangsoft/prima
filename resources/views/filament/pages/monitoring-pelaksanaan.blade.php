<x-filament-panels::page>
    <style>
        .mp-wrap { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .mp-filters { display:flex; flex-wrap:wrap; align-items:flex-end; gap:16px; margin-bottom:20px; }
        .mp-field { display:flex; flex-direction:column; gap:6px; }
        .mp-field > label { font-size:13px; font-weight:600; color:#374151; }
        .mp-field select {
            min-width:220px; padding:9px 12px; font-size:14px; color:#1f2937;
            background:#fff; border:1px solid #d1d5db; border-radius:8px;
        }
        .mp-excel {
            display:inline-flex; align-items:center; gap:8px; padding:10px 16px;
            font-size:14px; font-weight:600; color:#fff; background:#16a34a;
            border:0; border-radius:8px; cursor:pointer;
        }
        .mp-excel:hover { background:#15803d; }
        .mp-table-scroll { overflow-x:auto; }
        .mp-table { width:100%; border-collapse:collapse; font-size:13px; white-space:nowrap; }
        .mp-table th, .mp-table td { border:1px solid #eef0f3; padding:10px 14px; text-align:center; }
        .mp-table thead th { background:#f8fafc; color:#6b7280; font-weight:600; }
        .mp-table td.mp-left, .mp-table th.mp-left { text-align:left; }
        .mp-table tbody tr:nth-child(even) { background:#fbfcfd; }
        .mp-skema { color:#3b5bd9; font-weight:600; text-decoration:none; white-space:normal; }
        .mp-detail {
            display:inline-block; padding:6px 14px; font-size:12px; font-weight:600;
            color:#fff; background:#3b5bd9; border-radius:6px; text-decoration:none;
        }
        .mp-detail:hover { background:#2f49b0; }
        .mp-empty { padding:22px; text-align:center; color:#9ca3af; }
        .mp-badge-head { display:inline-flex; align-items:center; gap:6px; }
        .mp-dot { width:16px; height:16px; border-radius:9999px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:11px; }
    </style>

    <div class="mp-wrap">
        <div class="mp-filters">
            <div class="mp-field">
                <label for="mp-kategori">Jenis Kegiatan</label>
                <select id="mp-kategori" wire:model.live="kategori">
                    @foreach ($this->getKategoriOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mp-field">
                <label for="mp-tahun">Tahun Pelaksanaan</label>
                <select id="mp-tahun" wire:model.live="tahun">
                    @foreach ($this->getTahunOptions() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="mp-excel" wire:click="downloadExcel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 3v5h5M9 13l6 4M15 13l-6 4"/>
                </svg>
                Excel
            </button>
        </div>

        <div class="mp-table-scroll">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2" class="mp-left">Skema</th>
                        <th rowspan="2"><span class="mp-badge-head">Didanai <span class="mp-dot" style="background:#16a34a;">&check;</span></span></th>
                        <th rowspan="2"><span class="mp-badge-head">Dibatalkan <span class="mp-dot" style="background:#dc2626;">&times;</span></span></th>
                        <th colspan="2">Revisi Proposal</th>
                        <th colspan="2">Laporan Kemajuan</th>
                        <th colspan="2">Laporan Akhir</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>Sudah Unggah</th><th>Belum Unggah</th>
                        <th>Sudah Unggah</th><th>Belum Unggah</th>
                        <th>Sudah Unggah</th><th>Belum Unggah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rekap as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="mp-left"><a class="mp-skema" href="{{ $r['url'] }}" wire:navigate>{{ $r['skema'] }}</a></td>
                            <td>{{ $r['didanai'] }}</td>
                            <td>{{ $r['dibatalkan'] }}</td>
                            <td>{{ $r['revisi_sudah'] }}</td>
                            <td>{{ $r['revisi_belum'] }}</td>
                            <td>{{ $r['kemajuan_sudah'] }}</td>
                            <td>{{ $r['kemajuan_belum'] }}</td>
                            <td>{{ $r['akhir_sudah'] }}</td>
                            <td>{{ $r['akhir_belum'] }}</td>
                            <td><a class="mp-detail" href="{{ $r['url'] }}" wire:navigate>Detail</a></td>
                        </tr>
                    @empty
                        <tr><td class="mp-empty" colspan="11">Belum ada usulan berjalan pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
