<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b5bd9" stroke-width="2">
                <path d="M3 21h18M4 21V10l8-5 8 5v11M9 21v-6h6v6"/></svg>
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#2f43b8;">Profil Lembaga Penelitian</h3>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;">
            @foreach ($rows as $i => [$label, $value])
                <div @style(['grid-column:1 / -1' => $i >= 4])>
                    <div style="font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#9ca3af;">{{ $label }}</div>
                    <div style="margin-top:2px;font-size:13px;font-weight:600;color:#374151;">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
