<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b5bd9" stroke-width="2">
                <path d="M3 11l14-6v14L3 13v-2zM3 11v2M17 8a3 3 0 0 1 0 8"/></svg>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#2f43b8;">Pengumuman</h3>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
            @forelse ($this->getAnnouncements() as $a)
                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#3b5bd9;">
                        @if ($a->disematkan)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2Z"/></svg>
                        @endif
                        {{ $a->tanggal_terbit?->translatedFormat('j F Y') }}
                    </div>
                    <div style="margin-top:4px;font-weight:700;color:#111827;">{{ $a->judul }}</div>
                    <div style="margin-top:4px;font-size:13px;color:#6b7280;line-height:1.5;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($a->isi), 160) }}
                    </div>
                    @if ($a->lampiranUrl())
                        <a href="{{ $a->lampiranUrl() }}" target="_blank" rel="noopener"
                           style="margin-top:8px;display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:#3b5bd9;text-decoration:none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
                            Unduh PDF
                        </a>
                    @endif
                </div>
            @empty
                <div style="font-size:13px;color:#6b7280;">Belum ada pengumuman.</div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
