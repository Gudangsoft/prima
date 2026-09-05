<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Filament\Exports\ProposalExporter;
use App\Filament\Resources\ProposalResource;
use App\Filament\Widgets\MonitoringUsulanStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;

    public function getTitle(): string
    {
        return auth()->user()->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin', 'reviewer'])
            ? 'Monitoring Usulan'
            : 'Usulan Saya';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Usulan Baru')
                ->visible(fn (): bool => auth()->user()->can('create', ProposalResource::getModel())),

            Actions\ExportAction::make()
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->exporter(ProposalExporter::class)
                ->visible(fn (): bool => auth()->user()->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin'])),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MonitoringUsulanStats::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 1;
    }
}
