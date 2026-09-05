<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProgramStudiResource\Pages;

use App\Filament\Resources\ProgramStudiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgramStudi extends ListRecords
{
    protected static string $resource = ProgramStudiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Prodi Baru'),
        ];
    }
}
