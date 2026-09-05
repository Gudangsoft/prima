<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Support;

use App\Enums\FundingStatus;
use App\Enums\ReviewRecommendation;
use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

/**
 * Skema form yang dipakai bersama oleh aksi alur kerja usulan, baik sebagai
 * table row action maupun page header action.
 *
 * @phpstan-return list<Component>
 */
final class WorkflowForms
{
    /** Catatan persetujuan (opsional saat setujui, wajib saat tolak). */
    public static function catatanKeputusan(bool $wajib): array
    {
        return [
            Textarea::make('catatan')
                ->label('Catatan')
                ->rows(3)
                ->maxLength(2000)
                ->required($wajib)
                ->helperText($wajib ? 'Wajib diisi saat menolak usulan.' : 'Opsional.'),
        ];
    }

    public static function penugasanReviewer(): array
    {
        return [
            Select::make('reviewers')
                ->label('Reviewer')
                ->multiple()
                ->required()
                ->searchable()
                ->preload()
                ->options(fn (): array => User::query()
                    ->role('reviewer')
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->helperText('Boleh memilih lebih dari satu reviewer.'),
        ];
    }

    public static function penilaianReviewer(): array
    {
        return [
            TextInput::make('skor')
                ->label('Skor')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(100)
                ->helperText('Rentang 0–100.'),

            Select::make('rekomendasi')
                ->label('Rekomendasi')
                ->options(ReviewRecommendation::options())
                ->required()
                ->native(false),

            Textarea::make('catatan')
                ->label('Catatan penilaian')
                ->rows(4)
                ->maxLength(3000),
        ];
    }

    public static function penetapanPendanaan(): array
    {
        return [
            Select::make('status_danai')
                ->label('Status pendanaan')
                ->options(FundingStatus::options())
                ->required()
                ->native(false)
                ->live(),

            TextInput::make('jumlah_dana')
                ->label('Jumlah dana (Rp)')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->visible(fn (Get $get): bool => $get('status_danai') !== FundingStatus::TidakDidanai->value)
                ->required(fn (Get $get): bool => $get('status_danai') !== null
                    && $get('status_danai') !== FundingStatus::TidakDidanai->value),

            TextInput::make('sk_pendanaan')
                ->label('Nomor SK penetapan')
                ->maxLength(255),

            FileUpload::make('file_sk')
                ->label('Berkas SK (PDF, opsional)')
                ->disk('local')
                ->directory('sk-pendanaan')
                ->visibility('private')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize((int) config('sip2m.uploads.output_max_kb')),
        ];
    }
}
