<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Jenjang;
use App\Filament\Imports\ProgramStudiImporter;
use App\Filament\Pages\SinkronisasiDosen;
use App\Filament\Resources\ProgramStudiResource\Pages;
use App\Models\ProgramStudi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;

/**
 * "Sinkronisasi Prodi" (grup Data Pendukung) — master program studi.
 * Karena tidak ada koneksi PDDIKTI, sinkronisasi dilakukan lewat impor CSV.
 */
class ProgramStudiResource extends Resource
{
    protected static ?string $model = ProgramStudi::class;

    protected static ?string $slug = 'program-studi';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Data Pendukung';

    protected static ?string $navigationLabel = 'Sinkronisasi Prodi';

    protected static ?string $modelLabel = 'Program Studi';

    protected static ?string $pluralModelLabel = 'Program Studi';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode')
                ->label('Kode prodi')
                ->required()
                ->maxLength(20)
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('jenjang')
                ->options(Jenjang::options())
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('nama')
                ->label('Nama program studi')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('fakultas')
                ->maxLength(255),

            Forms\Components\Toggle::make('aktif')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari Nama Prodi atau Kode Prodi')
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('kode')->label('Kode Prodi')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('nama')->label('Nama Prodi')->searchable()->sortable()->wrap(),

                Tables\Columns\TextColumn::make('jenjang')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (Jenjang $state): string => $state->value),

                Tables\Columns\TextColumn::make('aktif')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Non Aktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('fakultas')->searchable()->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),
                Tables\Columns\TextColumn::make('dosen_count')
                    ->label('Dosen')
                    ->counts('dosen')
                    ->alignCenter()
                    ->url(fn (ProgramStudi $record): string => SinkronisasiDosen::urlUntukProdi($record->id)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')->options(Jenjang::options()),
                Tables\Filters\TernaryFilter::make('aktif'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label('Impor CSV')
                    ->importer(ProgramStudiImporter::class)
                    ->color('primary')
                    ->visible(fn (): bool => auth()->user()->can('create', ProgramStudi::class)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgramStudi::route('/'),
            'create' => Pages\CreateProgramStudi::route('/create'),
            'edit' => Pages\EditProgramStudi::route('/{record}/edit'),
        ];
    }
}
