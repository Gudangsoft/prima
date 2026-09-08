<x-filament-panels::page
    @class([
        'fi-resource-view-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    <style>
        .pv-hero { background:#fff; border:1px solid #e9ebef; border-radius:14px; padding:22px 24px; margin-bottom:18px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .pv-chips { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
        .pv-chip { display:inline-flex; align-items:center; padding:3px 11px; border-radius:9999px; font-size:11.5px; font-weight:700; }
        .pv-chip-outline { background:#fff; border:1px solid #d1d5db; color:#4b5563; }
        .pv-title { font-size:21px; font-weight:800; color:#1f2937; line-height:1.35; margin:0 0 14px; }
        .pv-meta { display:flex; flex-wrap:wrap; gap:22px; row-gap:10px; }
        .pv-meta-item { display:flex; align-items:flex-start; gap:8px; min-width:150px; }
        .pv-meta-item svg { flex-shrink:0; margin-top:2px; color:#9ca3af; }
        .pv-meta-label { font-size:10.5px; color:#9ca3af; text-transform:uppercase; letter-spacing:.03em; line-height:1.3; }
        .pv-meta-value { font-size:13px; font-weight:700; color:#1f2937; line-height:1.35; }
        .pv-dana .pv-meta-value { color:#15803d; font-size:15px; }

        .pv-stepper-card { background:#fff; border:1px solid #e9ebef; border-radius:14px; padding:20px 24px 16px; margin-bottom:18px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow-x:auto; }
        .pv-stepper { display:flex; align-items:flex-start; min-width:640px; }
        .pv-step { flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; position:relative; }
        .pv-step-dot {
            width:26px; height:26px; border-radius:9999px; display:flex; align-items:center; justify-content:center;
            font-size:11px; font-weight:800; background:#e5e7eb; color:#9ca3af; border:2px solid #e5e7eb; z-index:1;
        }
        .pv-step.is-done .pv-step-dot { background:#3b5bd9; border-color:#3b5bd9; color:#fff; }
        .pv-step.is-current .pv-step-dot { background:#fff; border-color:#3b5bd9; color:#3b5bd9; box-shadow:0 0 0 4px #e0e7ff; }
        .pv-step-line { position:absolute; top:13px; left:-50%; width:100%; height:2px; background:#e5e7eb; z-index:0; }
        .pv-step:first-child .pv-step-line { display:none; }
        .pv-step.is-done .pv-step-line, .pv-step.is-current .pv-step-line { background:#3b5bd9; }
        .pv-step-label { margin-top:7px; font-size:10.5px; font-weight:600; color:#9ca3af; line-height:1.3; max-width:88px; }
        .pv-step.is-done .pv-step-label, .pv-step.is-current .pv-step-label { color:#1f2937; }

        .pv-rejected { display:flex; align-items:flex-start; gap:12px; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:16px 18px; margin-bottom:18px; }
        .pv-rejected svg { flex-shrink:0; color:#dc2626; }
        .pv-rejected-title { font-size:13.5px; font-weight:800; color:#991b1b; margin-bottom:2px; }
        .pv-rejected-note { font-size:13px; color:#7f1d1d; line-height:1.5; }
    </style>

    @php
        $kat = $record->scheme?->kategori;
        $statusColor = match ($record->status->color()) {
            'success' => ['bg' => '#dcfce7', 'fg' => '#15803d'],
            'warning' => ['bg' => '#fef3c7', 'fg' => '#92400e'],
            'danger' => ['bg' => '#fee2e2', 'fg' => '#b91c1c'],
            'primary', 'info' => ['bg' => '#e0e7ff', 'fg' => '#3730a3'],
            default => ['bg' => '#e5e7eb', 'fg' => '#374151'],
        };
        $katColor = match ($kat?->color()) {
            'success' => ['bg' => '#dcfce7', 'fg' => '#15803d'],
            'warning' => ['bg' => '#fef3c7', 'fg' => '#92400e'],
            'danger' => ['bg' => '#fee2e2', 'fg' => '#b91c1c'],
            'primary', 'info' => ['bg' => '#e0e7ff', 'fg' => '#3730a3'],
            default => ['bg' => '#e5e7eb', 'fg' => '#374151'],
        };

        $order = [
            \App\Enums\ProposalStatus::Draft,
            \App\Enums\ProposalStatus::Submitted,
            \App\Enums\ProposalStatus::ApprovedLppm,
            \App\Enums\ProposalStatus::UnderReview,
            \App\Enums\ProposalStatus::Funded,
            \App\Enums\ProposalStatus::InProgress,
            \App\Enums\ProposalStatus::Reported,
            \App\Enums\ProposalStatus::OutputValidated,
        ];
        $currentIndex = array_search($record->status, $order, true);
        $isRejected = $record->status === \App\Enums\ProposalStatus::Rejected;
        $rejectionNote = $isRejected
            ? $record->statusHistories()->where('status', \App\Enums\ProposalStatus::Rejected->value)->latest('created_at')->first()?->catatan
            : null;
    @endphp

    <div class="pv-hero">
        <div class="pv-chips">
            @if ($kat)
                <span class="pv-chip" style="background:{{ $katColor['bg'] }};color:{{ $katColor['fg'] }};">{{ $kat->label() }}</span>
            @endif
            <span class="pv-chip pv-chip-outline">{{ $record->scheme?->nama_skema ?? '—' }}</span>
            <span class="pv-chip" style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['fg'] }};">{{ $record->status->label() }}</span>
        </div>

        <h1 class="pv-title">{{ $record->judul }}</h1>

        <div class="pv-meta">
            <div class="pv-meta-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.5-9 6v2h18v-2c0-3.5-4-6-9-6Z"/></svg>
                <div>
                    <div class="pv-meta-label">Ketua Pengusul</div>
                    <div class="pv-meta-value">{{ $record->submitter?->name ?? '—' }}</div>
                </div>
            </div>
            <div class="pv-meta-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <div>
                    <div class="pv-meta-label">Tahun Pelaksanaan</div>
                    <div class="pv-meta-value">{{ $record->tahun_anggaran }}</div>
                </div>
            </div>
            <div class="pv-meta-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                <div>
                    <div class="pv-meta-label">Terakhir Diperbarui</div>
                    <div class="pv-meta-value">{{ $record->updated_at?->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            @if ($record->fundingDecision?->jumlah_dana)
                <div class="pv-meta-item pv-dana">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
                    <div>
                        <div class="pv-meta-label">Dana Disetujui</div>
                        <div class="pv-meta-value">Rp {{ number_format((float) $record->fundingDecision->jumlah_dana, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($isRejected)
        <div class="pv-rejected">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
            <div>
                <div class="pv-rejected-title">Usulan Ditolak</div>
                <div class="pv-rejected-note">{{ $rejectionNote ?: 'Tidak ada catatan alasan penolakan.' }}</div>
            </div>
        </div>
    @else
        <div class="pv-stepper-card">
            <div class="pv-stepper">
                @foreach ($order as $i => $status)
                    <div class="pv-step {{ $i < $currentIndex ? 'is-done' : ($i === $currentIndex ? 'is-current' : '') }}">
                        <div class="pv-step-dot">
                            <div class="pv-step-line"></div>
                            @if ($i < $currentIndex)
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <div class="pv-step-label">{{ $status->label() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        @if ($this->hasInfolist())
            {{ $this->infolist }}
        @else
            <div
                wire:key="{{ $this->getId() }}.forms.{{ $this->getFormStatePath() }}"
            >
                {{ $this->form }}
            </div>
        @endif
    @endif

    @if (count($relationManagers))
        <x-filament-panels::resources.relation-managers
            :active-locale="isset($activeLocale) ? $activeLocale : null"
            :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
            :content-tab-label="$this->getContentTabLabel()"
            :content-tab-icon="$this->getContentTabIcon()"
            :content-tab-position="$this->getContentTabPosition()"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        >
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    @if ($this->hasInfolist())
                        {{ $this->infolist }}
                    @else
                        {{ $this->form }}
                    @endif
                </x-slot>
            @endif
        </x-filament-panels::resources.relation-managers>
    @endif
</x-filament-panels::page>
