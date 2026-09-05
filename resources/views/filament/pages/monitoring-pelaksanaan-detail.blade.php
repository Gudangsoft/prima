<x-filament-panels::page>
    <style>
        .mpd-wrap { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .mpd-banner {
            display:block; margin:0 auto 22px; max-width:520px; padding:12px 18px; text-align:center;
            font-size:15px; font-weight:700; color:#fff; background:#3b5bd9; border-radius:8px;
        }
        .mpd-table-scroll { overflow-x:auto; }
        .mpd-table { width:100%; border-collapse:collapse; font-size:13px; }
        .mpd-table th, .mpd-table td { border:1px solid #eef0f3; padding:10px 14px; vertical-align:top; }
        .mpd-table thead th { background:#f8fafc; color:#6b7280; font-weight:600; text-align:center; }
        .mpd-table th.mpd-left { text-align:left; }
        .mpd-num, .mpd-status, .mpd-berkas, .mpd-act { text-align:center; white-space:nowrap; }
        .mpd-judul { min-width:260px; }
        .mpd-pengusul { min-width:150px; }
        .mpd-tag {
            display:inline-block; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; color:#fff;
        }
        .mpd-tag.ok { background:#16a34a; }
        .mpd-tag.no { background:#6b7280; }
        .mpd-dl {
            display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:6px;
            font-size:12px; font-weight:600; color:#fff; background:#ef4444; text-decoration:none;
        }
        .mpd-dl:hover { background:#dc2626; }
        .mpd-detail {
            display:inline-block; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600;
            color:#fff; background:#3b5bd9; text-decoration:none;
        }
        .mpd-detail:hover { background:#2f49b0; }
        .mpd-muted { color:#9ca3af; }
        .mpd-empty { padding:22px; text-align:center; color:#9ca3af; }
    </style>

    <div class="mpd-wrap">
        <span class="mpd-banner">{{ $scheme->nama_skema }}</span>

        <div class="mpd-table-scroll">
            <table class="mpd-table">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2" class="mpd-left">Pengusul</th>
                        <th rowspan="2" class="mpd-left">Judul</th>
                        <th colspan="3">Revisi Proposal</th>
                        <th colspan="3">Laporan Kemajuan</th>
                        <th colspan="2">Laporan Akhir</th>
                    </tr>
                    <tr>
                        <th>Status</th><th>Berkas</th><th>Action</th>
                        <th>Status</th><th>Berkas</th><th>Action</th>
                        <th>Status</th><th>Berkas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $i => $r)
                        <tr>
                            <td class="mpd-num">{{ $i + 1 }}</td>
                            <td class="mpd-pengusul">
                                {{ $r['pengusul'] }}
                                @if ($r['nidn'])
                                    <div class="mpd-muted" style="font-size:12px;">NIDN {{ $r['nidn'] }}</div>
                                @endif
                            </td>
                            <td class="mpd-judul">{{ $r['judul'] }}</td>

                            {{-- Revisi Proposal --}}
                            <td class="mpd-status">
                                <span class="mpd-tag {{ $r['revisi_ada'] ? 'ok' : 'no' }}">
                                    {{ $r['revisi_ada'] ? 'Sudah Unggah' : 'Belum Unggah' }}
                                </span>
                            </td>
                            <td class="mpd-berkas">
                                @if ($r['revisi_url'])
                                    <a class="mpd-dl" href="{{ $r['revisi_url'] }}" target="_blank" rel="noopener">Download</a>
                                @else
                                    <span class="mpd-muted">&ndash;</span>
                                @endif
                            </td>
                            <td class="mpd-act"><a class="mpd-detail" href="{{ $r['detail_url'] }}" wire:navigate>Detail</a></td>

                            {{-- Laporan Kemajuan --}}
                            <td class="mpd-status">
                                <span class="mpd-tag {{ $r['kemajuan_ada'] ? 'ok' : 'no' }}">
                                    {{ $r['kemajuan_ada'] ? 'Sudah Unggah' : 'Belum Unggah' }}
                                </span>
                            </td>
                            <td class="mpd-berkas">
                                @if ($r['kemajuan_url'])
                                    <a class="mpd-dl" href="{{ $r['kemajuan_url'] }}" target="_blank" rel="noopener">Download</a>
                                @else
                                    <span class="mpd-muted">&ndash;</span>
                                @endif
                            </td>
                            <td class="mpd-act"><a class="mpd-detail" href="{{ $r['detail_url'] }}" wire:navigate>Detail</a></td>

                            {{-- Laporan Akhir --}}
                            <td class="mpd-status">
                                <span class="mpd-tag {{ $r['akhir_ada'] ? 'ok' : 'no' }}">
                                    {{ $r['akhir_ada'] ? 'Sudah Unggah' : 'Belum Unggah' }}
                                </span>
                            </td>
                            <td class="mpd-berkas">
                                @if ($r['akhir_url'])
                                    <a class="mpd-dl" href="{{ $r['akhir_url'] }}" target="_blank" rel="noopener">Download</a>
                                @else
                                    <span class="mpd-muted">&ndash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="mpd-empty" colspan="11">Belum ada usulan berjalan pada skema ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
