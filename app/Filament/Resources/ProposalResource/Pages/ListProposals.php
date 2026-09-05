<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Filament\Resources\ProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Usulan Baru')
                ->visible(fn (): bool => auth()->user()->can('create', ProposalResource::getModel())),
        ];
    }
}
