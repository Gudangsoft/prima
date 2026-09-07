<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\User\ImportDosenFromSinta;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importDosen')
                ->label('Import Dosen')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(fn (): bool => auth()->user()?->can('users.create') ?? false)
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File Export SINTA (CSV)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Unggah file "Export Author" dari SINTA apa adanya (kolom NIDN, NAMA, PRODI, dst.). Data yang sudah ada (dicocokkan lewat SINTA ID) akan diperbarui, bukan diduplikasi.')
                        ->disk('local')
                        ->directory('imports'),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['file']);

                    $result = app(ImportDosenFromSinta::class)($path);

                    Storage::disk('local')->delete($data['file']);

                    $body = "Dibuat: {$result->created} akun baru. Diperbarui: {$result->updated} akun.";

                    if ($result->skipped !== []) {
                        $body .= ' Dilewati: '.count($result->skipped).' baris ('.implode('; ', array_slice($result->skipped, 0, 5)).').';
                    }

                    Notification::make()
                        ->title('Import dosen selesai')
                        ->body($body)
                        ->success()
                        ->persistent()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
