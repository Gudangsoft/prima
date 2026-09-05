<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Models\FundingDecision;
use App\Models\Proposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan angka usulan untuk Admin LPPM & Pimpinan.
 */
class ProposalStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    protected function getStats(): array
    {
        $menunggu = Proposal::query()
            ->whereIn('status', [
                ProposalStatus::Submitted->value,
                ProposalStatus::ApprovedLppm->value,
                ProposalStatus::UnderReview->value,
            ])->count();

        $didanai = Proposal::query()
            ->whereIn('status', [
                ProposalStatus::Funded->value,
                ProposalStatus::InProgress->value,
                ProposalStatus::Reported->value,
                ProposalStatus::OutputValidated->value,
            ])->count();

        $ditolak = Proposal::query()->where('status', ProposalStatus::Rejected->value)->count();
        $selesai = Proposal::query()->where('status', ProposalStatus::OutputValidated->value)->count();

        $totalDana = (float) FundingDecision::query()->sum('jumlah_dana');

        return [
            Stat::make('Total Usulan', Proposal::query()->count())
                ->description('Seluruh tahun anggaran')
                ->icon('heroicon-o-document-text'),

            Stat::make('Dalam Proses', $menunggu)
                ->description('Diajukan / disetujui / dinilai')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Usulan Didanai', $didanai)
                ->description('Termasuk yang sedang berjalan')
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('Dana Tersalur', 'Rp '.number_format($totalDana, 0, ',', '.'))
                ->description('Akumulasi seluruh SK pendanaan')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Luaran Tervalidasi', $selesai)
                ->description('Kegiatan selesai tuntas')
                ->color('primary')
                ->icon('heroicon-o-trophy'),

            Stat::make('Ditolak', $ditolak)
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
