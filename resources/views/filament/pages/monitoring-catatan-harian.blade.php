<x-filament-panels::page>
    <style>
        .ch-wrap { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ch-filters { display:flex; flex-wrap:wrap; align-items:flex-end; gap:16px; margin-bottom:16px; }
        .ch-field { display:flex; flex-direction:column; gap:6px; }
        .ch-field > label { font-size:13px; font-weight:600; color:#374151; }
        .ch-field select, .ch-field input {
            min-width:220px; padding:9px 12px; font-size:14px; color:#1f2937;
            background:#fff; border:1px solid #d1d5db; border-radius:8px;
        }
        .ch-field input[readonly] { background:#f3f4f6; color:#6b7280; }
        .ch-excel {
            display:inline-flex; align-items:center; gap:8px; padding:10px 16px;
            font-size:14px; font-weight:600; color:#fff; background:#16a34a; border:0; border-radius:8px; cursor:pointer;
        }
        .ch-excel:hover { background:#15803d; }
        .ch-search { margin-bottom:14px; }
        .ch-search input { width:320px; max-width:100%; padding:9px 12px; font-size:14px; border:1px solid #d1d5db; border-radius:8px; }
        .ch-table-scroll { overflow-x:auto; }
        .ch-table { width:100%; border-collapse:collapse; font-size:13px; }
        .ch-table th, .ch-table td { border-bottom:1px solid #eef0f3; padding:12px 14px; text-align:left; }
        .ch-table thead th { background:#f8fafc; color:#6b7280; font-weight:600; }
        .ch-table tbody tr:hover { background:#fafbfc; }
        .ch-skema { color:#3b5bd9; font-weight:600; text-decoration:none; }
        .ch-detail {
            display:inline-block; padding:6px 16px; font-size:12px; font-weight:600;
            color:#fff; background:#3b5bd9; border-radius:6px; text-decoration:none;
        }
        .ch-detail:hover { background:#2f49b0; }
        .ch-empty { padding:22px; text-align:center; color:#9ca3af; }
    </style>

    <div class="ch-wrap">
        <div class="ch-filters">
            <div class="ch-field">
                <label for="ch-kategori">Jenis Kegiatan</label>
                <select id="ch-kategori" wire:model.live="kategori">
                    @foreach ($this->getKategoriOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ch-field">
                <label for="ch-tahun">Tahun Pelaksanaan</label>
                <select id="ch-tahun" wire:model.live="tahun">
                    @foreach ($this->getTahunOptions() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ch-field">
                <label for="ch-pt">Nama PT</label>
                <input id="ch-pt" type="text" value="{{ $this->namaPt }}" readonly>
            </div>

            <button type="button" class="ch-excel" wire:click="downloadExcel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 3v5h5M9 13l6 4M15 13l-6 4"/>
                </svg>
                Excel
            </button>
        </div>

        <div class="ch-search">
            <input type="text" placeholder="Cari Nama..." wire:model.live.debounce.400ms="cari">
        </div>

        <div class="ch-table-scroll">
            <table class="ch-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Skema</th>
                        <th style="width:140px;">Jumlah</th>
                        <th style="width:120px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rekap as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><a class="ch-skema" href="{{ $r['url'] }}" wire:navigate>{{ $r['skema'] }}</a></td>
                            <td>{{ $r['jumlah'] }}</td>
                            <td><a class="ch-detail" href="{{ $r['url'] }}" wire:navigate>Detail</a></td>
                        </tr>
                    @empty
                        <tr><td class="ch-empty" colspan="4">Belum ada usulan berjalan pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
