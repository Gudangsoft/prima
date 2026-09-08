<x-filament-widgets::widget>
    <div style="background:#fff;border:1px solid #e9ebef;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <div style="display:flex;align-items:center;justify-content:space-between;
                    background:#2f43b8;color:#fff;padding:12px 18px;font-weight:700;font-size:14px;">
            Profil Saya
            <a href="{{ $editUrl }}" title="Ubah profil"
               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;
                      border-radius:8px;background:rgba(255,255,255,.18);color:#fff;text-decoration:none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
            </a>
        </div>

        <div style="padding:14px 16px;display:flex;gap:14px;align-items:center;">
            @if (! empty($avatarUrl))
                <img src="{{ $avatarUrl }}" alt="{{ $nama }}"
                     style="width:60px;height:60px;border-radius:9999px;object-fit:cover;background:#e5e7eb;flex-shrink:0;">
            @else
                <span style="width:60px;height:60px;border-radius:9999px;background:#e5e7eb;flex-shrink:0;
                             display:flex;align-items:center;justify-content:center;color:#9ca3af;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.5-9 6v2h18v-2c0-3.5-4-6-9-6Z"/>
                    </svg>
                </span>
            @endif

            <div style="min-width:0;">
                <div style="font-size:17px;font-weight:800;color:#1f2937;line-height:1.2;">{{ $nama }}</div>
                @if ($prodi)
                    <div style="font-size:12.5px;color:#4b5563;margin-top:2px;">{{ $prodi }}</div>
                @endif
                <div style="font-size:12.5px;color:#6b7280;">{{ $institusi }}</div>
                <span style="display:inline-block;margin-top:6px;padding:2px 9px;border-radius:9999px;
                             background:#dcfce7;color:#15803d;font-size:10.5px;font-weight:700;">Aktif Mengajar</span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(88px,1fr));border-top:1px solid #eef0f3;">
            @foreach ($stats as $s)
                <div style="padding:11px 8px;text-align:center;border-right:1px solid #eef0f3;border-top:1px solid #eef0f3;margin-top:-1px;">
                    <div style="font-size:10.5px;color:#6b7280;line-height:1.3;">{{ $s['label'] }}</div>
                    <div style="margin-top:5px;font-size:16px;font-weight:800;color:#2f43b8;line-height:1;">{{ $s['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
