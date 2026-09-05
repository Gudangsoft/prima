<x-filament-panels::page>
    <style>
        .chd-wrap { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .chd-banner {
            display:block; margin:0 auto 22px; max-width:520px; padding:12px 18px; text-align:center;
            font-size:15px; font-weight:700; color:#fff; background:#3b5bd9; border-radius:8px;
        }
        .chd-table-scroll { overflow-x:auto; }
        .chd-table { width:100%; border-collapse:collapse; font-size:13px; }
        .chd-table th, .chd-table td { border-bottom:1px solid #eef0f3; padding:12px 14px; text-align:left; vertical-align:top; }
        .chd-table thead th { background:#f8fafc; color:#6b7280; font-weight:600; }
        .chd-pengusul { min-width:170px; font-weight:600; color:#1f2937; }
        .chd-judul { min-width:280px; color:#3b5bd9; }
        .chd-dana { margin-top:10px; color:#3b5bd9; font-size:12px; }
        .chd-ket { min-width:190px; color:#374151; line-height:1.7; }
        .chd-ket b { color:#3b5bd9; }
        .chd-detail {
            display:inline-block; padding:6px 16px; font-size:12px; font-weight:600;
            color:#fff; background:#3b5bd9; border-radius:6px; text-decoration:none;
        }
        .chd-detail:hover { background:#2f49b0; }
        .chd-empty { padding:22px; text-align:center; color:#9ca3af; }
    </style>

    <div class="chd-wrap">
        <span class="chd-banner">{{ $scheme->nama_skema }}</span>

        <div class="chd-table-scroll">
            <table class="chd-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Pengusul</th>
                        <th>Judul</th>
                        <th>Keterangan</th>
                        <th style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="chd-pengusul">{{ $r['pengusul'] }}</td>
                            <td class="chd-judul">
                                {{ $r['judul'] }}
                                @if ($r['dana'] !== null)
                                    <div class="chd-dana">Dana: Rp. {{ number_format((float) $r['dana'], 2, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="chd-ket">
                                Jumlah Catatan : <b>{{ $r['jumlah_catatan'] }}</b><br>
                                Persentase Capaian : <b>{{ $r['persentase'] }} %</b>
                            </td>
                            <td><a class="chd-detail" href="{{ $r['detail_url'] }}" wire:navigate>Detail</a></td>
                        </tr>
                    @empty
                        <tr><td class="chd-empty" colspan="5">Belum ada usulan berjalan pada skema ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
