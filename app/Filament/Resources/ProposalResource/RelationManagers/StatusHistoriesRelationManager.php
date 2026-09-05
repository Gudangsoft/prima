<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\RelationManagers;

use App\Enums\ProposalStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Riwayat status usulan — hanya-baca. Baris dibuat otomatis oleh ProposalObserver.
 */
class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Riwayat Status';

    protected static ?string $icon = 'heroicon-o-clock';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                    ->color(fn (ProposalStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Oleh')
                    ->placeholder('Sistem'),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
