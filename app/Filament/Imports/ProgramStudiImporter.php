<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\Jenjang;
use App\Models\ProgramStudi;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProgramStudiImporter extends Importer
{
    protected static ?string $model = ProgramStudi::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('kode')
                ->label('Kode prodi')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:20'])
                ->example('55201'),

            ImportColumn::make('nama')
                ->label('Nama program studi')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Teknik Informatika'),

            ImportColumn::make('jenjang')
                ->rules(['nullable', 'string'])
                ->castStateUsing(function (?string $state): string {
                    $state = strtoupper(trim((string) $state));

                    return Jenjang::tryFrom($state)?->value ?? 'S1';
                })
                ->example('S1'),

            ImportColumn::make('fakultas')
                ->rules(['nullable', 'string', 'max:255'])
                ->example('Fakultas Teknik'),

            ImportColumn::make('aktif')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('1'),
        ];
    }

    public function resolveRecord(): ProgramStudi
    {
        // Cocokkan berdasarkan kode prodi -> update bila ada, buat bila tidak.
        return ProgramStudi::firstOrNew(['kode' => trim((string) $this->data['kode'])]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor Program Studi selesai: '.number_format($import->successful_rows).' baris berhasil.';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' baris gagal.';
        }

        return $body;
    }
}
