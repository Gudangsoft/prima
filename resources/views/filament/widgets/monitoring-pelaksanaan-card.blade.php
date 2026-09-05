<x-filament-widgets::widget>
    <div style="border-radius:16px;padding:20px;color:#fff;background:linear-gradient(120deg,#3b5bd9,#4666e6);">
        <div style="display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                <rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 13l2 2 4-4"/></svg>
            <h3 style="margin:0;font-size:15px;font-weight:800;">Monitoring Pelaksanaan Kegiatan</h3>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;">
            @php
                $tints = [
                    'amber' => ['#fef3c7', '#d97706'],
                    'blue'  => ['#dbeafe', '#2563eb'],
                    'green' => ['#dcfce7', '#16a34a'],
                ];
            @endphp
            @foreach ($cards as $c)
                @php [$bg, $fg] = $tints[$c['tint']]; @endphp
                <div style="border-radius:12px;background:#fff;padding:16px;color:#374151;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:{{ $bg }};color:{{ $fg }};">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        </span>
                        <span style="font-size:13px;font-weight:700;color:#111827;">{{ $c['title'] }}</span>
                    </div>
                    <div style="margin-top:12px;display:flex;align-items:flex-end;justify-content:space-between;">
                        <div>
                            <div style="font-size:22px;font-weight:800;color:#111827;">{{ $c['belum'] }}</div>
                            <div style="font-size:11px;color:#6b7280;">Belum Submit / Belum Selesai</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:22px;font-weight:800;color:#3b5bd9;">{{ $c['sudah'] }}</div>
                            <div style="font-size:11px;color:#6b7280;">Sudah Submit</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
