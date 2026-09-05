<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalSchemeResource\Pages;

use App\Filament\Resources\ProposalSchemeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProposalScheme extends EditRecord
{
    protected static string $resource = ProposalSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
