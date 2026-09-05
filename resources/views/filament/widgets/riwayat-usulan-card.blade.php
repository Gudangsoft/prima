<x-filament-widgets::widget>
    <div style="background:#fff;border:1px solid #e9ebef;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <div style="background:#2f43b8;color:#fff;padding:12px 18px;font-weight:700;font-size:14px;">Riwayat Usulan</div>

        <div style="padding:4px 16px 10px;">
            @foreach ($groups as $g)
                <div style="padding:11px 0;{{ ! $loop->last ? 'border-bottom:1px solid #eef0f3;' : '' }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:12.5px;font-weight:700;color:#1f2937;">{{ $g['label'] }}</span>
                        <a href="{{ $moreUrl }}" wire:navigate
                           style="font-size:11.5px;color:#6b7280;text-decoration:none;">more..</a>
                    </div>

                    @if ($g['items']->isEmpty())
                        <div style="margin-top:6px;font-size:11.5px;color:#9ca3af;padding-left:15px;">Tidak ada</div>
                    @else
                        <ul style="margin:6px 0 0;padding-left:16px;list-style:disc;">
                            @foreach ($g['items'] as $it)
                                <li style="margin:4px 0;">
                                    <a href="{{ $it['url'] }}" wire:navigate
                                       style="font-size:12px;color:#3b5bd9;text-decoration:none;line-height:1.4;">{{ $it['judul'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
