<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource;
use App\Models\Proposal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * "Usulan Terbaru" pada dasbor pengawas (Admin LPPM / Pimpinan).
 */
class LatestProposalsTable extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Usulan Terbaru';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Proposal::query()
                    ->with(['scheme', 'submitter'])
                    ->latest('updated_at')
                    ->limit(8),
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('submitter.name')
                    ->label('Pengusul'),

                Tables\Columns\TextColumn::make('scheme.nama_skema')
                    ->label('Skema')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                    ->color(fn (ProposalStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since(),
            ])
            ->recordUrl(fn (Proposal $record): string => ProposalResource::getUrl('view', ['record' => $record]));
    }
}
