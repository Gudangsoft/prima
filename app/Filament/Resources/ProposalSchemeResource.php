<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Kategori;
use App\Filament\Resources\ProposalSchemeResource\Pages;
use App\Models\ProposalScheme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProposalSchemeResource extends Resource
{
    protected static ?string $model = ProposalScheme::class;

    protected static ?string $slug = 'skema';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Data Pendukung';

    protected static ?string $navigationLabel = 'Skema Usulan';

    protected static ?string $modelLabel = 'Skema Usulan';

    protected static ?string $pluralModelLabel = 'Skema Usulan';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_skema')
                ->label('Nama skema')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('kategori')
                ->label('Kategori')
                ->options(Kategori::options())
                ->required()
                ->native(false),

            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(4)
                ->maxLength(2000)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('template_path')
                ->label('Template usulan')
                ->disk('public')
                ->directory('skema-template')
                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->maxSize(10240)
                ->downloadable()
                ->previewable(false)
                ->helperText('Opsional. PDF/Word, maks 10 MB — akan tersedia untuk diunduh dosen saat memilih skema ini.')
                ->columnSpanFull(),

            Forms\Components\Toggle::make('aktif')
                ->label('Aktif')
                ->helperText('Hanya skema aktif yang bisa dipilih dosen saat mengajukan usulan.')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_skema')
                    ->label('Nama skema')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (Kategori $state): string => $state->label())
                    ->color(fn (Kategori $state): string => $state->color()),

                Tables\Columns\TextColumn::make('proposals_count')
                    ->label('Jml usulan')
                    ->counts('proposals')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('template_path')
                    ->label('Template')
                    ->state(fn (ProposalScheme $record): bool => filled($record->template_path))
                    ->boolean(),

                Tables\Columns\ToggleColumn::make('aktif')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Kategori::options()),

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status aktif'),
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
            ->defaultSort('nama_skema');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProposalSchemes::route('/'),
            'create' => Pages\CreateProposalScheme::route('/create'),
            'edit' => Pages\EditProposalScheme::route('/{record}/edit'),
        ];
    }
}
