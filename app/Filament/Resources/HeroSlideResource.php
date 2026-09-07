<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Slider (banner) di header halaman publik. Maks 5 slide aktif yang tampil,
 * diurutkan lewat drag-and-drop pada tabel.
 */
class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $slug = 'slider';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Slider Beranda';

    protected static ?string $modelLabel = 'Slide';

    protected static ?string $pluralModelLabel = 'Slider Beranda';

    protected static ?int $navigationSort = 19;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('gambar')
                        ->label('Gambar')
                        ->image()
                        ->required()
                        ->disk('public')
                        ->directory('hero-slides')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '21:9'])
                        ->helperText('Disarankan gambar lanskap beresolusi tinggi (mis. 1920×960px).')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('judul')
                        ->label('Judul')
                        ->maxLength(255)
                        ->helperText('Opsional. Kosongkan untuk slide gambar polos.')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('subjudul')
                        ->label('Subjudul')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('tautan')
                        ->label('Tautan (opsional)')
                        ->url()
                        ->helperText('Kosongkan jika slide tidak perlu bisa diklik.'),

                    Forms\Components\Toggle::make('aktif')
                        ->label('Aktifkan')
                        ->default(true)
                        ->helperText('Nonaktifkan untuk menyembunyikan dari slider tanpa menghapus.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->placeholder('—')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\IconColumn::make('aktif')->label('Aktif')->boolean(),

                Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->since()->toggleable(isToggledHiddenByDefault: true),
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
            ->reorderable('urutan')
            ->defaultSort('urutan');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
