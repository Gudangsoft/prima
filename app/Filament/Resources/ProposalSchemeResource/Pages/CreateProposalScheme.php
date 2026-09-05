<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalSchemeResource\Pages;

use App\Filament\Resources\ProposalSchemeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProposalScheme extends CreateRecord
{
    protected static string $resource = ProposalSchemeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
