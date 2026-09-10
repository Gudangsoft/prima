@php
    $branding = \App\Support\Settings::branding();
    $logoSistem = $branding['logo_url'];
    $logoInstansi = $branding['logo_instansi_url'] ?? null;
@endphp

<div style="display:flex;align-items:center;gap:.65rem;height:100%;">
    <img src="{{ $logoSistem }}" alt="{{ $branding['app_name'] }}"
         style="height:100%;width:auto;max-width:180px;object-fit:contain;">

    @if ($logoInstansi)
        <span style="width:1px;align-self:stretch;margin:.35rem 0;background:currentColor;opacity:.2;"></span>
        <img src="{{ $logoInstansi }}" alt="Logo Instansi"
             style="height:100%;width:auto;max-width:180px;object-fit:contain;">
    @endif
</div>
