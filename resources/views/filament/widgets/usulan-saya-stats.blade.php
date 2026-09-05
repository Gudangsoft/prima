<x-filament-widgets::widget>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
        @foreach ($cards as $c)
            <a href="{{ $c['url'] }}" wire:navigate
               style="position:relative;display:block;text-decoration:none;color:#fff;overflow:hidden;
                      background:{{ $c['bg'] }};border-radius:14px;padding:18px 20px;
                      box-shadow:0 1px 2px rgba(0,0,0,.06);transition:transform .15s,box-shadow .15s;"
               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 22px rgba(0,0,0,.14)';"
               onmouseout="this.style.transform='none';this.style.boxShadow='0 1px 2px rgba(0,0,0,.06)';">
                <div style="font-size:30px;font-weight:800;line-height:1;">{{ number_format($c['value'], 0, ',', '.') }}</div>
                <div style="margin-top:8px;font-size:14px;font-weight:700;">{{ $c['label'] }}</div>
                <div style="font-size:11px;opacity:.85;margin-top:2px;">Usulan Anda</div>
                <span style="position:absolute;right:16px;bottom:14px;opacity:.28;">
                    <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $c['icon'] }}"/>
                    </svg>
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
