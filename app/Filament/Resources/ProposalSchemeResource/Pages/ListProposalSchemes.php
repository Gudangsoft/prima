<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalSchemeResource\Pages;

use App\Filament\Resources\ProposalSchemeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProposalSchemes extends ListRecords
{
    protected static string $resource = ProposalSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
