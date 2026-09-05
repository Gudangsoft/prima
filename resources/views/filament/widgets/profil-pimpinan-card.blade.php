<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b5bd9" stroke-width="2">
                <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#2f43b8;">Profil Pimpinan Lembaga Penelitian</h3>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;">
            @foreach ($rows as [$label, $value])
                <div>
                    <div style="font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#9ca3af;">{{ $label }}</div>
                    <div style="margin-top:2px;font-size:13px;font-weight:600;color:#374151;">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
