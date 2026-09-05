<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\RelationManagers;

use App\Models\CatatanHarian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Catatan harian (logbook) pelaksanaan. Dosen pemilik menambah/mengubah entri
 * miliknya selama usulan berjalan; peran lain hanya membaca.
 */
class CatatanHarianRelationManager extends RelationManager
{
    protected static string $relationship = 'catatanHarian';

    protected static ?string $title = 'Catatan Harian';

    protected static ?string $icon = 'heroicon-o-calendar-days';

    private function bolehKelola(): bool
    {
        return auth()->user()->can('logbook', $this->getOwnerRecord());
    }

    public function canCreate(): bool
    {
        return $this->bolehKelola();
    }

    public function canEdit(Model $record): bool
    {
        return $this->bolehKelola() && $record->created_by === auth()->id();
    }

    public function canDelete(Model $record): bool
    {
        return $this->canEdit($record);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->required()
                ->default(now())
                ->native(false)
                ->maxDate(now()),

            Forms\Components\TextInput::make('persentase')
                ->label('Progres kumulatif (%)')
                ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

            Forms\Components\Textarea::make('kegiatan')
                ->label('Kegiatan')
                ->required()->rows(3)->maxLength(2000)->columnSpanFull(),

            Forms\Components\Textarea::make('capaian')
                ->label('Capaian / hasil')
                ->rows(2)->maxLength(2000)->columnSpanFull(),

            Forms\Components\FileUpload::make('berkas')
                ->label('Berkas pendukung (PDF)')
                ->disk('public')->directory('logbook')
                ->acceptedFileTypes(['application/pdf'])->maxSize(10240)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('kegiatan')->wrap()->limit(80),
                Tables\Columns\TextColumn::make('persentase')->label('Progres')->suffix('%')->alignCenter()->placeholder('—'),
                Tables\Columns\TextColumn::make('author.name')->label('Oleh')->placeholder('—')->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Catatan')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('unduh')
                    ->label('Berkas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (CatatanHarian $record): bool => filled($record->berkas))
                    ->url(fn (CatatanHarian $record): ?string => $record->berkasUrl())
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
