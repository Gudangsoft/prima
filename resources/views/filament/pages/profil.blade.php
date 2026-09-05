@php($h = $this->getHeaderData())
<x-filament-panels::page>
    {{-- Kartu ringkasan --}}
    <div style="border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);background:#fff;">
        <div style="height:96px;background:linear-gradient(110deg,#2f43b8,#3b5bd9 60%,#4666e6);"></div>
        <div style="padding:0 24px 20px;">
            <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px;margin-top:-40px;">
                @if ($h['avatarUrl'])
                    <img src="{{ $h['avatarUrl'] }}" alt="{{ $h['nama'] }}"
                         style="width:88px;height:88px;border-radius:9999px;object-fit:cover;border:4px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.15);">
                @else
                    <div style="width:88px;height:88px;border-radius:9999px;border:4px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.15);
                                background:#1f2f63;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;">
                        {{ $h['inisial'] }}
                    </div>
                @endif

                <div style="flex:1 1 240px;padding-bottom:4px;">
                    <div style="font-size:20px;font-weight:800;color:#111827;">{{ $h['nama'] }}</div>
                    <div style="font-size:13px;color:#6b7280;">{{ $h['email'] }}</div>
                    <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach ($h['roles'] as $r)
                            <span style="background:#eef2fe;color:#2f43b8;border-radius:9999px;padding:2px 10px;font-size:11px;font-weight:700;">{{ $r }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="margin-top:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
                @foreach ([
                    ['Jabatan', $h['jabatan'] ?: '—'],
                    ['Unit kerja', $h['unitKerja'] ?: '—'],
                    ['Bergabung', $h['bergabung'] ?: '—'],
                ] as [$label, $value])
                    <div style="border:1px solid #eef1f4;border-radius:10px;padding:10px 12px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af;">{{ $label }}</div>
                        <div style="margin-top:2px;font-size:13px;font-weight:600;color:#374151;">{{ $value }}</div>
                    </div>
                @endforeach
                <div style="border:1px solid #eef1f4;border-radius:10px;padding:10px 12px;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af;">Status</div>
                    <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:6px;">
                        <span style="font-size:11px;font-weight:700;border-radius:9999px;padding:2px 8px;{{ $h['emailVerified'] ? 'background:#dcfce7;color:#16a34a;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $h['emailVerified'] ? 'Email terverifikasi' : 'Email belum verif.' }}
                        </span>
                        <span style="font-size:11px;font-weight:700;border-radius:9999px;padding:2px 8px;{{ $h['otpProfil'] ? 'background:#dcfce7;color:#16a34a;' : 'background:#fef9c3;color:#ca8a04;' }}">
                            {{ $h['otpProfil'] ? 'Kontak lengkap' : 'No. HP kosong' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form edit --}}
    <x-filament-panels::form wire:submit="save" class="mt-6">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
