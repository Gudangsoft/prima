<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b5bd9" stroke-width="2">
                    <rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 3v4h6V3M8 12h8M8 16h5"/></svg>
                <h3 style="margin:0;font-size:15px;font-weight:800;color:#2f43b8;">Monitoring Usulan</h3>
            </div>
            <a href="{{ $detailUrl }}" style="font-size:12px;font-weight:700;color:#3b5bd9;text-decoration:none;">Lihat Detail &rarr;</a>
        </div>

        <div style="margin-top:20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:24px 20px;">
            @foreach ($items as [$label, $value])
                <div>
                    <div style="font-size:30px;font-weight:800;line-height:1;color:#2f43b8;">{{ number_format($value, 0, ',', '.') }}</div>
                    <div style="margin-top:6px;font-size:12px;color:#6b7280;">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
