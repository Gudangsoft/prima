<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\RelationManagers;

use App\Actions\Proposal\RecordOutputValidation;
use App\Enums\OutputType;
use App\Enums\OutputValidationStatus;
use App\Models\Output;
use App\Models\Proposal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Bukti luaran. Dosen pemilik menambah/mengubah selama belum tervalidasi;
 * Admin LPPM memvalidasi atau meminta revisi.
 */
class OutputsRelationManager extends RelationManager
{
    protected static string $relationship = 'outputs';

    protected static ?string $title = 'Luaran';

    protected static ?string $icon = 'heroicon-o-trophy';

    protected function canManage(): bool
    {
        return auth()->user()->can('manageOutputs', $this->getOwnerRecord());
    }

    public function canCreate(): bool
    {
        return $this->canManage();
    }

    public function canEdit(Model $record): bool
    {
        return $this->canManage() && $record->status_validasi !== OutputValidationStatus::Valid;
    }

    public function canDelete(Model $record): bool
    {
        return $this->canEdit($record);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jenis_luaran')
                ->label('Jenis luaran')
                ->options(OutputType::options())
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('judul_luaran')
                ->label('Judul / nama luaran')
                ->maxLength(255),

            Forms\Components\TextInput::make('tautan')
                ->label('Tautan (URL)')
                ->url()
                ->maxLength(500),

            Forms\Components\FileUpload::make('bukti_file')
                ->label('Berkas bukti (PDF)')
                ->disk('local')
                ->directory('outputs')
                ->visibility('private')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize((int) config('sip2m.uploads.output_max_kb')),
        ]);
    }

    public function table(Table $table): Table
    {
        /** @var Proposal $proposal */
        $proposal = $this->getOwnerRecord();
        $user = auth()->user();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_luaran')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (OutputType $state): string => $state->label()),

                Tables\Columns\TextColumn::make('judul_luaran')
                    ->label('Judul')
                    ->wrap()
                    ->limit(60)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status_validasi')
                    ->label('Validasi')
                    ->badge()
                    ->formatStateUsing(fn (OutputValidationStatus $state): string => $state->label())
                    ->color(fn (OutputValidationStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('validatedBy.name')
                    ->label('Divalidasi oleh')
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Luaran')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status_validasi'] = OutputValidationStatus::Pending->value;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Output $record): bool => filled($record->bukti_file))
                    ->url(fn (Output $record): string => route('download.output', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('validasi')
                    ->label('Validasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (): bool => $user->can('validateOutput', $proposal))
                    ->form([
                        Forms\Components\Select::make('status_validasi')
                            ->label('Hasil validasi')
                            ->options([
                                OutputValidationStatus::Valid->value => OutputValidationStatus::Valid->label(),
                                OutputValidationStatus::Revisi->value => OutputValidationStatus::Revisi->label(),
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('catatan_validasi')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(2000),
                    ])
                    ->action(function (Output $record, array $data) use ($user): void {
                        app(RecordOutputValidation::class)(
                            $record,
                            $user,
                            OutputValidationStatus::from($data['status_validasi']),
                            $data['catatan_validasi'] ?? null,
                        );
                        Notification::make()->title('Validasi luaran tersimpan')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
