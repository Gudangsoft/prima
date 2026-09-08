@php
    $user = auth()->user();
    $roles = $user?->getRoleNames() ?? collect();
@endphp

@if ($user && $roles->isNotEmpty())
    @php
        $aktif = $user->activeRole();
        $label = $aktif ? (\App\Enums\Role::tryFrom($aktif)?->label() ?? $aktif) : null;
        $lainnya = $roles->reject(fn (string $r) => $r === $aktif)->values();
    @endphp

    <style>
        .arb-item:hover { background:#f3f4f6; }
        [x-cloak] { display:none !important; }
    </style>

    <div
        @if ($lainnya->isNotEmpty()) x-data="{ open: false }" @click.outside="open = false" @endif
        style="position:relative;"
    >
        <button
            type="button"
            @if ($lainnya->isNotEmpty()) @click="open = !open" @endif
            style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:9999px;
                   background:#eef2fe;border:1px solid #dbe3fd;color:#2f43b8;font-size:12.5px;font-weight:700;
                   white-space:nowrap;{{ $lainnya->isNotEmpty() ? 'cursor:pointer;' : 'cursor:default;' }}"
        >
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;">
                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.5-9 6v2h18v-2c0-3.5-4-6-9-6Z"/>
            </svg>
            {{ $label }}
            @if ($lainnya->isNotEmpty())
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            @endif
        </button>

        @if ($lainnya->isNotEmpty())
            <div
                x-show="open" x-cloak x-transition
                style="position:absolute;right:0;top:calc(100% + 6px);min-width:190px;background:#fff;
                       border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 10px 28px rgba(15,23,42,.14);
                       padding:6px;z-index:30;"
            >
                <div style="padding:6px 10px 4px;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.03em;">
                    Ganti Peran Aktif
                </div>
                @foreach ($lainnya as $r)
                    <a href="{{ route('switch-role', $r) }}" class="arb-item"
                       style="display:block;padding:8px 10px;border-radius:7px;font-size:13px;font-weight:600;
                              color:#1f2937;text-decoration:none;">
                        {{ \App\Enums\Role::tryFrom($r)?->label() ?? $r }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
