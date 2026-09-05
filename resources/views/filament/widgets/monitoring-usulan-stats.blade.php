<x-filament-widgets::widget>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:16px;">
        @foreach ($cards as $c)
            <a href="{{ $c['url'] }}" wire:navigate
               style="position:relative;display:block;text-decoration:none;color:inherit;
                      background:#fff;border:1px solid #e9ebef;border-radius:12px;padding:18px 20px;
                      box-shadow:0 1px 2px rgba(0,0,0,.04);transition:box-shadow .15s,transform .15s;"
               onmouseover="this.style.boxShadow='0 6px 18px rgba(0,0,0,.10)';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,.04)';this.style.transform='none';">
                <div style="font-size:13px;color:#6b7280;padding-right:52px;">{{ $c['label'] }}</div>
                <div style="margin-top:8px;font-size:28px;font-weight:800;color:#1f2937;line-height:1;">
                    {{ number_format($c['value'], 0, ',', '.') }}
                </div>
                <span style="position:absolute;top:16px;right:18px;display:flex;align-items:center;justify-content:center;
                             width:44px;height:44px;border-radius:9999px;background:{{ $c['bg'] }};color:#fff;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $c['icon'] }}"/>
                    </svg>
                </span>
            </a>
        @endforeach
    </div>

    <div style="margin-top:24px;">
        <div style="font-size:14px;font-weight:700;color:#3b5bd9;margin-bottom:10px;">Rekap Usulan</div>

        <div style="overflow-x:auto;background:#fff;border:1px solid #e9ebef;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <table style="width:100%;border-collapse:collapse;font-size:13px;white-space:nowrap;">
                <thead>
                    <tr style="background:#f8fafc;color:#6b7280;text-align:center;">
                        <th style="padding:12px 16px;font-weight:600;text-align:left;">Nama Skema</th>
                        <th style="padding:12px 16px;font-weight:600;">Usulan Draft</th>
                        <th style="padding:12px 16px;font-weight:600;">Usulan Dikirim Pengusul</th>
                        <th style="padding:12px 16px;font-weight:600;">Usulan Belum Ditinjau LPPM</th>
                        <th style="padding:12px 16px;font-weight:600;">Usulan Disetujui LPPM</th>
                        <th style="padding:12px 16px;font-weight:600;">Usulan Tidak Disetujui LPPM</th>
                        <th style="padding:12px 16px;font-weight:600;">Usulan Didanai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap as $r)
                        <tr style="border-top:1px solid #eef0f3;text-align:center;color:#374151;">
                            <td style="padding:12px 16px;text-align:left;">
                                <a href="{{ $r['url'] }}" wire:navigate
                                   style="color:#3b5bd9;font-weight:600;text-decoration:none;white-space:normal;">{{ $r['skema'] }}</a>
                            </td>
                            <td style="padding:12px 16px;">{{ $r['draft'] }}</td>
                            <td style="padding:12px 16px;">{{ $r['dikirim'] }}</td>
                            <td style="padding:12px 16px;">{{ $r['belum_ditinjau'] }}</td>
                            <td style="padding:12px 16px;">{{ $r['disetujui'] }}</td>
                            <td style="padding:12px 16px;">{{ $r['ditolak'] }}</td>
                            <td style="padding:12px 16px;">{{ $r['didanai'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:18px 16px;text-align:center;color:#9ca3af;">Belum ada usulan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-widgets::widget>
