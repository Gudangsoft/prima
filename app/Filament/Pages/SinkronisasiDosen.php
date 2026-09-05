<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Imports\DosenImporter;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;

/**
 * "Sinkronisasi Dosen" (grup Data Pendukung) — impor / pemutakhiran akun dosen
 * dari berkas CSV (pengganti tarik data PDDIKTI). Baris dicocokkan berdasarkan
 * NIDN; akun baru otomatis berperan "dosen".
 */
class SinkronisasiDosen extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';

    protected static ?string $navigationGroup = 'Data Pendukung';

    protected static ?string $navigationLabel = 'Sinkronisasi Dosen';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Sinkronisasi Dosen';

    protected static string $view = 'filament.pages.sinkronisasi-dosen';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('create', User::class) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make('imporDosen')
                ->label('Impor CSV Dosen')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(DosenImporter::class),

            Action::make('unduhTemplate')
                ->label('Unduh template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn () => response()->streamDownload(
                    fn () => print ("nidn,name,email,phone_number,jabatan,kompetensi,kode_prodi\n"
                        ."0401019001,\"Dr. Budi Santoso, M.Kom.\",budi@kampus.ac.id,081234567890,Lektor,\"Rekayasa Perangkat Lunak\",55201\n"),
                    'template-dosen.csv',
                    ['Content-Type' => 'text/csv'],
                )),
        ];
    }
}
