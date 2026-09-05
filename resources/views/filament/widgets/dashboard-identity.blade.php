<x-filament-widgets::widget>
    <div style="position:relative;overflow:hidden;border-radius:16px;padding:28px 32px;color:#fff;
                background:linear-gradient(110deg,#2f43b8 0%,#3b5bd9 55%,#4666e6 100%);">

        <div style="position:absolute;right:-70px;top:-100px;width:230px;height:230px;border-radius:9999px;background:rgba(255,255,255,.10);"></div>
        <div style="position:absolute;bottom:-90px;right:150px;width:190px;height:190px;border-radius:9999px;background:rgba(255,255,255,.06);"></div>

        <svg width="22" height="22" viewBox="0 0 24 24" fill="#fcd34d" style="position:absolute;right:34px;top:24px;">
            <path d="M12 2l1.9 5.5L19 9l-5.1 1.5L12 16l-1.9-5.5L5 9l5.1-1.5L12 2Z"/></svg>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#fde68a" style="position:absolute;right:120px;top:66px;">
            <path d="M12 2l1.9 5.5L19 9l-5.1 1.5L12 16l-1.9-5.5L5 9l5.1-1.5L12 2Z"/></svg>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="#fcd34d" style="position:absolute;right:70px;bottom:22px;">
            <path d="M12 2l1.9 5.5L19 9l-5.1 1.5L12 16l-1.9-5.5L5 9l5.1-1.5L12 2Z"/></svg>

        <div style="position:relative;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:24px;">
            <div style="flex:1 1 240px;min-width:0;">
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);
                             border:1px solid rgba(255,255,255,.25);border-radius:8px;padding:4px 10px;font-size:12px;font-weight:600;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    {{ $tanggal }}
                </span>
                <h2 style="margin:14px 0 2px;font-size:28px;font-weight:800;line-height:1.15;">Selamat Datang, {{ $nama }}!</h2>
                <p style="margin:0;color:rgba(255,255,255,.8);font-size:15px;">Have a nice {{ $hariEn }}!</p>
            </div>

            @if (! empty($avatarUrl))
                <img src="{{ $avatarUrl }}" alt="{{ $nama }}"
                     style="width:76px;height:76px;border-radius:9999px;object-fit:cover;border:3px solid rgba(255,255,255,.5);flex-shrink:0;">
            @else
                <svg width="150" height="104" viewBox="0 0 160 112" fill="none" style="flex-shrink:0;">
                    <ellipse cx="80" cy="104" rx="52" ry="7" fill="#fff" opacity=".15"/>
                    <circle cx="80" cy="30" r="16" fill="#fff" opacity=".95"/>
                    <path d="M52 96c0-18 12-32 28-32s28 14 28 32H52Z" fill="#fff" opacity=".95"/>
                    <g transform="rotate(-8 98 58)">
                        <rect x="98" y="58" width="34" height="24" rx="3" fill="#fff" opacity=".95"/>
                        <path d="M104 64h20M104 70h20M104 76h13" stroke="#3b5bd9" stroke-width="2"/>
                    </g>
                </svg>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
