<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan usulan milik dosen yang sedang login.
 */
class MyProposalsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->isDosen()
            && ! $user->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']);
    }

    protected function getStats(): array
    {
        $mine = Proposal::query()->where('user_id', auth()->id());

        return [
            Stat::make('Draf', (clone $mine)->where('status', ProposalStatus::Draft->value)->count())
                ->color('gray')
                ->icon('heroicon-o-pencil'),

            Stat::make('Dalam Proses', (clone $mine)->whereIn('status', [
                ProposalStatus::Submitted->value,
                ProposalStatus::ApprovedLppm->value,
                ProposalStatus::UnderReview->value,
            ])->count())->color('warning')->icon('heroicon-o-clock'),

            Stat::make('Didanai / Berjalan', (clone $mine)->whereIn('status', [
                ProposalStatus::Funded->value,
                ProposalStatus::InProgress->value,
                ProposalStatus::Reported->value,
                ProposalStatus::OutputValidated->value,
            ])->count())->color('success')->icon('heroicon-o-check-circle'),

            Stat::make('Ditolak', (clone $mine)->where('status', ProposalStatus::Rejected->value)->count())
                ->color('danger'),
        ];
    }
}
