<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Proposal\RecordOutputValidation;
use App\Enums\OutputType;
use App\Enums\OutputValidationStatus;
use App\Filament\Pages\Concerns\OversightTablePage;
use App\Filament\Resources\ProposalResource;
use App\Models\Output;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * "Validasi Luaran" (grup Monitoring): daftar seluruh bukti luaran yang
 * menunggu validasi Admin LPPM, lengkap dengan aksi validasi langsung.
 */
class ValidasiLuaran extends OversightTablePage
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Validasi Luaran';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Validasi Luaran';

    public static function getNavigationBadge(): ?string
    {
        $pending = Output::query()->where('status_validasi', OutputValidationStatus::Pending->value)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Output::query()->with('proposal.submitter'))
            ->columns([
                Tables\Columns\TextColumn::make('proposal.judul')->label('Usulan')->limit(50)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('proposal.submitter.name')->label('Pengusul')->searchable(),
                Tables\Columns\TextColumn::make('jenis_luaran')
                    ->label('Jenis')->badge()
                    ->formatStateUsing(fn (OutputType $state): string => $state->label()),
                Tables\Columns\TextColumn::make('judul_luaran')->label('Judul Luaran')->limit(40)->placeholder('—'),
                Tables\Columns\TextColumn::make('status_validasi')
                    ->label('Status')->badge()
                    ->formatStateUsing(fn (OutputValidationStatus $state): string => $state->label())
                    ->color(fn (OutputValidationStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('created_at')->label('Diunggah')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_validasi')
                    ->label('Status validasi')
                    ->options(OutputValidationStatus::options())
                    ->default(OutputValidationStatus::Pending->value),
                Tables\Filters\SelectFilter::make('jenis_luaran')
                    ->label('Jenis luaran')
                    ->options(OutputType::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('unduh')
                    ->label('Bukti')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Output $record): bool => filled($record->bukti_file))
                    ->url(fn (Output $record): string => route('download.output', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('validasi')
                    ->label('Validasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Output $record): bool => auth()->user()->can('validateOutput', $record->proposal))
                    ->form([
                        Forms\Components\Select::make('status_validasi')
                            ->label('Hasil validasi')
                            ->options([
                                OutputValidationStatus::Valid->value => OutputValidationStatus::Valid->label(),
                                OutputValidationStatus::Revisi->value => OutputValidationStatus::Revisi->label(),
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('catatan_validasi')->label('Catatan')->rows(3)->maxLength(2000),
                    ])
                    ->action(function (Output $record, array $data): void {
                        app(RecordOutputValidation::class)(
                            $record,
                            auth()->user(),
                            OutputValidationStatus::from($data['status_validasi']),
                            $data['catatan_validasi'] ?? null,
                        );
                        Notification::make()->title('Validasi luaran tersimpan')->success()->send();
                    }),

                Tables\Actions\Action::make('buka')
                    ->label('Usulan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Output $record): string => ProposalResource::getUrl('view', ['record' => $record->proposal_id])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
