@php($rowStyle = 'display:grid;grid-template-columns:230px 16px 1fr;gap:8px;padding:10px 0;align-items:start;')
<x-filament-panels::page>
    <div style="font-size:15px;font-weight:800;letter-spacing:.02em;color:#374151;text-transform:uppercase;">
        Profil Lembaga Penelitian / Pengabdian kepada Masyarakat
    </div>

    {{-- Tab Penelitian / Pengabdian --}}
    <div style="margin-top:12px;display:flex;justify-content:flex-end;">
        <div style="display:inline-flex;background:#eef1f6;border-radius:12px;padding:4px;gap:4px;">
            @foreach (['penelitian' => 'Penelitian', 'pengabdian' => 'Pengabdian'] as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')"
                    style="border:0;cursor:pointer;border-radius:9px;padding:8px 22px;font-size:13px;font-weight:700;
                           {{ $tab === $key ? 'background:#3b5bd9;color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.15);' : 'background:transparent;color:#3b5bd9;' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Profil Lembaga --}}
    <div wire:key="lembaga-{{ $tab }}" style="margin-top:8px;border:1px solid #e9ebef;border-radius:14px;background:#fff;padding:22px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:.03em;">
                Profil Lembaga {{ $labelKategori }}
            </h3>
            @if ($canEditLembaga)
                <a href="{{ $editLembagaUrl }}"
                   style="background:#f0b429;color:#fff;font-weight:700;font-size:13px;border-radius:8px;padding:8px 20px;text-decoration:none;">
                    Edit
                </a>
            @endif
        </div>

        <dl style="margin:14px 0 0;">
            @foreach ($lembaga as $label => $value)
                <div style="{{ $rowStyle }}{{ ! $loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                    <dt style="color:#6b7280;font-size:13px;">{{ $label }}</dt>
                    <dd style="margin:0;color:#9ca3af;">:</dd>
                    <dd style="margin:0;font-weight:700;color:#1f2937;font-size:13px;">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- Profil Pimpinan --}}
    <div wire:key="pimpinan-{{ $tab }}" style="margin-top:16px;border:1px solid #e9ebef;border-radius:14px;background:#fff;padding:22px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:.03em;">
                Profil Pimpinan Lembaga {{ $labelKategori }}
            </h3>
            @if ($canEditPimpinan)
                <a href="{{ $editPimpinanUrl }}"
                   style="background:#f0b429;color:#fff;font-weight:700;font-size:13px;border-radius:8px;padding:8px 20px;text-decoration:none;">
                    Edit
                </a>
            @endif
        </div>

        <dl style="margin:14px 0 0;">
            @foreach ($pimpinan as $label => $value)
                <div style="{{ $rowStyle }}{{ ! $loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                    <dt style="color:#6b7280;font-size:13px;">{{ $label }}</dt>
                    <dd style="margin:0;color:#9ca3af;">:</dd>
                    <dd style="margin:0;font-weight:700;color:#1f2937;font-size:13px;">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <p style="margin-top:12px;font-size:12px;color:#9ca3af;">
        Profil per kategori diubah di <strong>Pengaturan &rarr; Pengaturan Web</strong> (Super Admin).
        Pimpinan Penelitian dan Pengabdian bisa diisi orang yang berbeda; bila dikosongkan, memakai akun berperan <em>Pimpinan</em>.
    </p>
</x-filament-panels::page>
