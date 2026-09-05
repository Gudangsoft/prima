<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Proposal;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProposalExporter extends Exporter
{
    protected static ?string $model = Proposal::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('judul')->label('Judul'),
            ExportColumn::make('submitter.name')->label('Ketua Pengusul'),
            ExportColumn::make('submitter.nidn')->label('NIDN'),
            ExportColumn::make('scheme.nama_skema')->label('Skema'),
            ExportColumn::make('scheme.kategori')->label('Kategori')
                ->formatStateUsing(fn ($state): string => $state?->label() ?? ''),
            ExportColumn::make('tahun_anggaran')->label('Tahun Anggaran'),
            ExportColumn::make('status')->label('Status')
                ->formatStateUsing(fn ($state): string => $state?->label() ?? (string) $state),
            ExportColumn::make('fundingDecision.jumlah_dana')->label('Dana Disetujui'),
            ExportColumn::make('fundingDecision.sk_pendanaan')->label('Nomor SK'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor usulan selesai: '.number_format($export->successful_rows).' baris.';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' baris gagal.';
        }

        return $body;
    }
}
