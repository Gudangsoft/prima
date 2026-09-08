<div style="font-size:13px;color:#374151;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:6px;">
        <span style="font-weight:700;color:#15803d;display:flex;align-items:center;gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 13 4 4L19 7"/></svg>
            Skema yang Eligible
        </span>
        <span style="background:#16a34a;color:#fff;border-radius:9999px;min-width:22px;text-align:center;padding:1px 7px;font-size:11px;font-weight:700;">{{ count($data['eligible']) }}</span>
    </div>

    @if (count($data['eligible']))
        <ul style="margin:0 0 16px;padding-left:0;list-style:none;">
            @foreach ($data['eligible'] as $nama)
                <li style="padding:8px 14px;border-bottom:1px solid #f1f2f4;">{{ $nama }}</li>
            @endforeach
        </ul>
    @else
        <p style="margin:0 0 16px;padding:8px 14px;color:#9ca3af;">Tidak ada skema yang eligible.</p>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:6px;">
        <span style="font-weight:700;color:#b91c1c;display:flex;align-items:center;gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            Skema yang Tidak Eligible
        </span>
        <span style="background:#dc2626;color:#fff;border-radius:9999px;min-width:22px;text-align:center;padding:1px 7px;font-size:11px;font-weight:700;">{{ count($data['tidak']) }}</span>
    </div>

    @if (count($data['tidak']))
        <div>
            @foreach ($data['tidak'] as $item)
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:6px;">
                    <div style="font-weight:600;color:#92400e;">{{ $item['nama'] }}</div>
                    <div style="margin-top:2px;color:#78716c;">{{ $item['alasan'] }}</div>
                </div>
            @endforeach
        </div>
    @else
        <p style="margin:0;padding:8px 14px;color:#9ca3af;">Tidak ada skema yang tidak eligible.</p>
    @endif
</div>
