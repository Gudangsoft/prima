<x-filament-panels::page>
    <style>
        .chl-wrap { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .chl-head { font-size:13px; font-weight:700; letter-spacing:.04em; color:#374151; text-transform:uppercase; }
        .chl-rule { border:0; border-top:1px solid #e9ebef; margin:14px 0 18px; }
        .chl-judul { font-size:15px; font-weight:700; color:#1f2937; margin-bottom:16px; }
        .chl-table-scroll { overflow-x:auto; }
        .chl-table { width:100%; border-collapse:collapse; font-size:13px; }
        .chl-table th, .chl-table td { border-bottom:1px solid #eef0f3; padding:12px 14px; text-align:left; vertical-align:top; }
        .chl-table thead th { background:#f8fafc; color:#6b7280; font-weight:600; }
        .chl-tanggal { white-space:nowrap; color:#3b5bd9; font-weight:600; }
        .chl-kegiatan { color:#374151; line-height:1.7; }
        .chl-persen { white-space:nowrap; text-align:right; }
        .chl-berkas {
            display:inline-flex; align-items:center; gap:6px; margin-top:10px; padding:6px 14px;
            font:inherit; font-size:12px; font-weight:600; color:#fff; background:#3b5bd9;
            border:0; border-radius:6px; text-decoration:none; cursor:pointer;
        }
        .chl-berkas:hover { background:#2f49b0; }
        .chl-files { margin-top:8px; background:#eef1f6; border-radius:8px; padding:12px 16px; }
        .chl-files ol { margin:0; padding-left:20px; }
        .chl-files li { margin:3px 0; }
        .chl-files a { color:#2563eb; text-decoration:underline; word-break:break-all; }
        .chl-empty { padding:22px; text-align:center; color:#9ca3af; }
        [x-cloak] { display:none !important; }
    </style>

    <div class="chl-wrap">
        <div class="chl-head">{{ $this->getHeadingLabel() }}</div>
        <hr class="chl-rule">
        <div class="chl-judul">{{ $proposal->judul }}</div>

        <div class="chl-table-scroll">
            <table class="chl-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No.</th>
                        <th style="width:140px;">Tanggal</th>
                        <th>Kegiatan</th>
                        <th style="width:110px;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->entries as $i => $e)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="chl-tanggal">{{ $e['tanggal']?->format('Y-m-d') }}</td>
                            <td class="chl-kegiatan">
                                {{ $e['kegiatan'] }}
                                @if (count($e['berkas']))
                                    <div x-data="{ open: false }" style="margin-top:10px;">
                                        <button type="button" class="chl-berkas" x-on:click="open = ! open">
                                            Berkas Pendukung
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <path d="M14 2v6h6"/>
                                            </svg>
                                        </button>
                                        <div class="chl-files" x-cloak x-show="open" x-transition>
                                            <ol>
                                                @foreach ($e['berkas'] as $b)
                                                    <li>
                                                        <a href="{{ $b['url'] }}" target="_blank" rel="noopener">{{ $b['name'] }}</a>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="chl-persen">{{ $e['persentase'] !== null ? $e['persentase'].' %' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td class="chl-empty" colspan="4">Belum ada catatan harian untuk usulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
