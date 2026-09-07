<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AnnouncementType;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * "Berita / Pengumuman" (grup Pengaturan). Konten tampil di dasbor & halaman
 * publik. Mendukung lampiran PDF.
 */
class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $slug = 'pengumuman';

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Berita / Pengumuman';

    protected static ?string $modelLabel = 'Pengumuman';

    protected static ?string $pluralModelLabel = 'Berita / Pengumuman';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\Radio::make('jenis')
                        ->label('Jenis konten')
                        ->options(AnnouncementType::options())
                        ->default(AnnouncementType::Pengumuman->value)
                        ->inline()
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('judul')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('gambar_sampul')
                        ->label('Gambar sampul')
                        ->image()
                        ->disk('public')
                        ->directory('announcements/sampul')
                        ->imageEditor()
                        ->visible(fn (Get $get): bool => $get('jenis') === AnnouncementType::Berita->value)
                        ->helperText('Ditampilkan sebagai gambar utama pada kartu Berita di halaman publik.')
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('isi')
                        ->label('Isi')
                        ->required()
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('tanggal_terbit')
                        ->label('Tanggal terbit')
                        ->required()
                        ->default(now())
                        ->native(false),

                    Forms\Components\FileUpload::make('lampiran_pdf')
                        ->label('Lampiran PDF')
                        ->disk('public')
                        ->directory('announcements')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->previewable(false)
                        ->helperText('Opsional. PDF, maks 10 MB — dapat diunduh dari halaman publik.'),

                    Forms\Components\Toggle::make('disematkan')
                        ->label('Sematkan (tampil di atas)')
                        ->default(false),

                    Forms\Components\Toggle::make('terbit')
                        ->label('Terbitkan')
                        ->default(true)
                        ->helperText('Nonaktifkan untuk menyimpan sebagai draf.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('disematkan')->label('')->boolean()
                    ->trueIcon('heroicon-s-bookmark')->falseIcon('')->trueColor('warning'),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (AnnouncementType $state): string => $state->label())
                    ->color(fn (AnnouncementType $state): string => match ($state) {
                        AnnouncementType::Berita => 'info',
                        AnnouncementType::Pengumuman => 'gray',
                    }),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')->searchable()->limit(60)->wrap(),

                Tables\Columns\TextColumn::make('tanggal_terbit')
                    ->label('Tanggal terbit')->date('d M Y')->sortable(),

                Tables\Columns\IconColumn::make('lampiran_pdf')
                    ->label('PDF')
                    ->state(fn (Announcement $record): bool => filled($record->lampiran_pdf))
                    ->boolean(),

                Tables\Columns\IconColumn::make('terbit')->label('Terbit')->boolean(),

                Tables\Columns\TextColumn::make('author.name')->label('Oleh')->placeholder('—')->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')->label('Jenis')->options(AnnouncementType::options()),
                Tables\Filters\TernaryFilter::make('terbit')->label('Status terbit'),
                Tables\Filters\TernaryFilter::make('disematkan')->label('Disematkan'),
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
            ->defaultSort('tanggal_terbit', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
