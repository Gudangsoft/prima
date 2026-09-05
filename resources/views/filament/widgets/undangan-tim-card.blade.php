<x-filament-widgets::widget>
    <div style="background:#fff;border:1px solid #e9ebef;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <div style="background:#f0b429;color:#fff;padding:12px 18px;font-weight:700;font-size:14px;">
            Undangan Anggota Tim
        </div>

        <div style="padding:6px 18px 14px;">
            @foreach ($invitations as $inv)
                <div style="padding:12px 0;{{ ! $loop->last ? 'border-bottom:1px solid #eef0f3;' : '' }}">
                    <div style="font-size:12.5px;font-weight:700;color:#1f2937;line-height:1.4;">{{ $inv['judul'] }}</div>
                    <div style="font-size:11.5px;color:#6b7280;margin-top:2px;">Ketua: {{ $inv['ketua'] }}</div>
                    @if ($inv['tugas'])
                        <div style="font-size:11.5px;color:#6b7280;margin-top:2px;">Tugas: {{ \Illuminate\Support\Str::limit($inv['tugas'], 90) }}</div>
                    @endif
                    <div style="margin-top:8px;display:flex;gap:8px;">
                        <button type="button" wire:click="respond({{ $inv['id'] }}, 'setuju')"
                                style="padding:5px 12px;border:0;border-radius:6px;font-size:12px;font-weight:600;color:#fff;background:#16a34a;cursor:pointer;">
                            Setujui
                        </button>
                        <button type="button" wire:click="respond({{ $inv['id'] }}, 'tolak')"
                                style="padding:5px 12px;border:0;border-radius:6px;font-size:12px;font-weight:600;color:#fff;background:#ef4444;cursor:pointer;">
                            Tolak
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
