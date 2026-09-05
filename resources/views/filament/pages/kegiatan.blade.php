<x-filament-panels::page>
    <style>
        .kg-tabs { display:flex; gap:6px; overflow-x:auto; border-bottom:1px solid #e5e7eb; padding-bottom:0; margin-bottom:20px; }
        .kg-tab {
            display:inline-flex; align-items:center; gap:6px; white-space:nowrap; text-decoration:none;
            padding:9px 16px; border-radius:8px 8px 0 0; font-size:13px; font-weight:600; color:#6b7280;
            border:1px solid transparent; border-bottom:none;
        }
        .kg-tab:hover { color:#2f43b8; background:#f3f4f6; }
        .kg-tab.active { color:#fff; background:#2f43b8; }
        .kg-card { background:#fff; border:1px solid #e9ebef; border-radius:12px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .kg-head { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:16px; }
        .kg-section { font-size:18px; font-weight:800; color:#334155; }
        .kg-filter label { display:block; font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px; }
        .kg-filter select { min-width:190px; padding:8px 12px; font-size:13px; border:1px solid #d1d5db; border-radius:8px; background:#fff; }
        .kg-scroll { overflow-x:auto; }
        .kg-table { width:100%; border-collapse:collapse; font-size:13px; }
        .kg-table th, .kg-table td { border-bottom:1px solid #eef0f3; padding:11px 13px; text-align:left; vertical-align:top; }
        .kg-table thead th { background:#f8fafc; color:#6b7280; font-weight:600; white-space:nowrap; }
        .kg-strong { font-weight:600; color:#1f2937; }
        .kg-primary { color:#3b5bd9; }
        .kg-badge { display:inline-block; padding:3px 9px; border-radius:9999px; font-size:11px; font-weight:700; }
        .kg-tag { display:inline-block; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:700; color:#fff; }
        .kg-btn { display:inline-block; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; color:#fff; background:#3b5bd9; text-decoration:none; white-space:nowrap; }
        .kg-btn:hover { background:#2f49b0; }
        .kg-dl { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:7px; background:#3b5bd9; color:#fff; text-decoration:none; }
        .kg-dl:hover { background:#2f49b0; }
        .kg-muted { color:#9ca3af; }
        .kg-info { background:#e0f2fe; color:#075985; border-radius:8px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
        .kg-empty { padding:26px; text-align:center; color:#9ca3af; }
    </style>

    @php($badgeStyle = fn (string $color) => match ($color) {
        'success' => 'background:#dcfce7;color:#15803d;',
        'warning' => 'background:#fef3c7;color:#92400e;',
        'danger'  => 'background:#fee2e2;color:#b91c1c;',
        'primary', 'info' => 'background:#e0e7ff;color:#3730a3;',
        default   => 'background:#e5e7eb;color:#374151;',
    })
    @php($tagBg = fn (string $color) => match ($color) {
        'success' => '#16a34a', 'warning' => '#f59e0b', 'danger' => '#ef4444', default => '#6b7280',
    })

    <div class="kg-tabs">
        @foreach (\App\Filament\Pages\Kegiatan::TABS as $key => $label)
            <a class="kg-tab {{ $this->tab === $key ? 'active' : '' }}" wire:navigate
               href="{{ \App\Filament\Pages\Kegiatan::urlFor($this->kategori, $key) }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5"/></svg>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="kg-card">
        <div class="kg-head">
            <div class="kg-section">{{ $this->getSectionHeading() }}</div>

            @if ($this->tab !== 'bimtek')
                <div class="kg-filter">
                    <label for="kg-tahun">Tahun Pelaksanaan</label>
                    <select id="kg-tahun" wire:model.live="tahun">
                        <option value="">Semua Tahun</option>
                        @foreach ($this->getTahunOptions() as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        @if ($this->tab === 'catatan')
            <div class="kg-info"><strong>Persentase Capaian</strong> diambil dari catatan terakhir yang ditambahkan.</div>
        @endif

        <div class="kg-scroll">
            <table class="kg-table">
                <thead>
                    <tr>
                        <th style="width:52px;">No</th>
                        @foreach ($this->columns as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            @foreach ($row as $c)
                                <td>
                                    @switch($c['kind'])
                                        @case('text')
                                            <span class="{{ ($c['strong'] ?? false) ? 'kg-strong' : '' }} {{ ($c['color'] ?? null) === 'primary' ? 'kg-primary' : '' }}">{{ $c['value'] }}</span>
                                            @break

                                        @case('badge')
                                            <span class="kg-badge" style="{{ $badgeStyle($c['color'] ?? 'gray') }}">{{ $c['value'] }}</span>
                                            @break

                                        @case('money')
                                            Rp {{ number_format($c['value'], 0, ',', '.') }}
                                            @break

                                        @case('multiline')
                                            @foreach ($c['value'] as $line)
                                                <div class="{{ $loop->first ? 'kg-strong' : '' }}">{{ $line }}</div>
                                            @endforeach
                                            @if (! empty($c['badge']))
                                                <span class="kg-tag" style="background:{{ $tagBg($c['badgeColor'] ?? 'gray') }};margin-top:4px;">{{ $c['badge'] }}</span>
                                            @endif
                                            @break

                                        @case('download')
                                            @if (! empty($c['url']))
                                                <a class="kg-dl" href="{{ $c['url'] }}" target="_blank" rel="noopener" title="Unduh">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                         stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>
                                                </a>
                                            @else
                                                <span class="kg-muted">&ndash;</span>
                                            @endif
                                            @break

                                        @case('button')
                                            <a class="kg-btn" href="{{ $c['url'] }}" wire:navigate>{{ $c['value'] }}</a>
                                            @break
                                    @endswitch
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td class="kg-empty" colspan="{{ count($this->columns) + 1 }}">
                                {{ $this->tab === 'bimtek' ? 'Data tidak tersedia!' : 'Belum ada data pada tab ini.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
