<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource;
use App\Models\Proposal;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Kartu "Monitoring Usulan" pada dasbor (gaya BIMA V2): tajuk + "Lihat Detail"
 * + grid angka. Cakupan disesuaikan peran (pengawas: se-institusi; dosen: milik sendiri).
 */
class MonitoringUsulanCard extends Widget
{
    protected static string $view = 'filament.widgets.monitoring-usulan-card';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    /** Dosen memakai UsulanSayaStats; kartu ini untuk peran pengawas. */
    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    private const DISETUJUI = [
        ProposalStatus::ApprovedLppm->value,
        ProposalStatus::UnderReview->value,
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    private const DIDANAI = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public function getViewData(): array
    {
        $base = fn (): Builder => $this->scopedQuery();

        return [
            'detailUrl' => ProposalResource::getUrl('index'),
            'items' => [
                ['Usulan Draft', $base()->where('status', ProposalStatus::Draft->value)->count()],
                ['Dikirim Pengusul', $base()->where('status', '!=', ProposalStatus::Draft->value)->count()],
                ['Belum Ditinjau LPPM', $base()->where('status', ProposalStatus::Submitted->value)->count()],
                ['Disetujui LPPM', $base()->whereIn('status', self::DISETUJUI)->count()],
                ['Ditolak LPPM', $base()->where('status', ProposalStatus::Rejected->value)->count()],
                ['Proposal Didanai', $base()->whereIn('status', self::DIDANAI)->count()],
            ],
        ];
    }

    private function scopedQuery(): Builder
    {
        $user = auth()->user();
        $q = Proposal::query();

        if (! $user->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin'])) {
            $q->where('user_id', $user->getKey());
        }

        return $q;
    }
}
