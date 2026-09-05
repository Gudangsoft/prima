<x-filament-panels::page>
    <div style="background:#fff;border:1px solid #e9ebef;border-radius:12px;padding:56px 24px;text-align:center;
                box-shadow:0 1px 2px rgba(0,0,0,.04);">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:9999px;
                     background:#eef1f6;color:#6b7280;margin-bottom:16px;">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </span>
        <div style="font-size:18px;font-weight:800;color:#1f2937;">Modul {{ $this->getModulLabel() }}</div>
        <p style="margin:8px auto 0;max-width:420px;font-size:13px;color:#6b7280;line-height:1.6;">
            Modul {{ $this->getModulLabel() }} belum tersedia di SIP2M. Saat ini sistem melayani
            alur usulan <strong>Penelitian</strong> dan <strong>Pengabdian kepada Masyarakat</strong>.
        </p>
    </div>
</x-filament-panels::page>
