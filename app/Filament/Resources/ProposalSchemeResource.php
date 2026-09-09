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
use Illuminate\Database\Eloquent\Builder;

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
            Forms\Components\Section::make('Informasi Dasar')
                ->icon('heroicon-o-identification')
                ->iconColor('primary')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('nama_skema')
                        ->label('Nama skema')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\Select::make('kategori')
                        ->label('Kategori')
                        ->options(Kategori::options())
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Biaya & Periode Pengajuan')
                ->icon('heroicon-o-banknotes')
                ->iconColor('success')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('dana_min')
                        ->label('Biaya Minimal')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->helperText('Opsional.'),

                    Forms\Components\TextInput::make('dana_max')
                        ->label('Biaya Maksimal')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->gte('dana_min')
                        ->validationMessages(['gte' => 'Harus ≥ biaya minimal.'])
                        ->helperText('Opsional.'),

                    Forms\Components\DatePicker::make('tanggal_buka')
                        ->label('Tanggal Buka')
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->helperText('Kosongkan = buka sekarang.'),

                    Forms\Components\DatePicker::make('tanggal_tutup')
                        ->label('Tanggal Tutup')
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->afterOrEqual('tanggal_buka')
                        ->validationMessages(['after_or_equal' => 'Harus setelah/sama dengan tanggal buka.'])
                        ->helperText('Otomatis hilang dari dosen setelah ini.'),
                ]),

            Forms\Components\Section::make('Target Luaran')
                ->icon('heroicon-o-flag')
                ->iconColor('warning')
                ->collapsible()
                ->schema([
                    Forms\Components\Repeater::make('luarans')
                        ->relationship()
                        ->hiddenLabel()
                        ->addActionLabel('Tambah target luaran')
                        ->orderColumn('urutan')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['jenis_luaran'] ?? null)
                        ->helperText('Luaran Wajib harus dipenuhi dosen; Luaran Tambahan bersifat opsional (bonus/nilai lebih usulan).')
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('jenis_luaran')
                                ->label('Jenis Luaran')
                                ->required()
                                ->maxLength(200)
                                ->placeholder('mis. Artikel Jurnal Nasional Terakreditasi SINTA 2')
                                ->columnSpan(2),

                            Forms\Components\ToggleButtons::make('wajib')
                                ->label('Sifat')
                                ->boolean('Wajib', 'Tambahan (opsional)')
                                ->colors([1 => 'danger', 0 => 'gray'])
                                ->default(true)
                                ->inline()
                                ->required(),

                            Forms\Components\TextInput::make('keterangan')
                                ->label('Keterangan')
                                ->maxLength(255)
                                ->placeholder('Opsional, mis. "minimal 1 per tahun"')
                                ->columnSpanFull(),
                        ]),
                ]),

            Forms\Components\Section::make('Berkas & Status')
                ->icon('heroicon-o-document-check')
                ->iconColor('gray')
                ->columns(3)
                ->schema([
                    Forms\Components\FileUpload::make('template_path')
                        ->label('Template usulan')
                        ->disk('public')
                        ->directory('skema-template')
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->previewable(false)
                        ->helperText('Opsional. PDF/Word, maks 10 MB — diunduh dosen saat memilih skema ini.')
                        ->columnSpan(2),

                    Forms\Components\Toggle::make('aktif')
                        ->label('Aktif')
                        ->helperText('Sakelar utama, di luar periode di atas.')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('luarans'))
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

                Tables\Columns\TextColumn::make('dana')
                    ->label('Biaya')
                    ->state(fn (ProposalScheme $record): string => $record->rentangDanaLabel() ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('luarans_count')
                    ->label('Target Luaran')
                    ->state(function (ProposalScheme $record): string {
                        $wajib = $record->luarans->where('wajib', true)->count();
                        $tambahan = $record->luarans->where('wajib', false)->count();

                        return $wajib === 0 && $tambahan === 0
                            ? '—'
                            : "{$wajib} Wajib · {$tambahan} Tambahan";
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('template_path')
                    ->label('Template')
                    ->state(fn (ProposalScheme $record): bool => filled($record->template_path))
                    ->boolean(),

                Tables\Columns\ToggleColumn::make('aktif')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('status_periode')
                    ->label('Status')
                    ->badge()
                    ->state(fn (ProposalScheme $record): string => $record->statusPeriode())
                    ->color(fn (ProposalScheme $record): string => $record->statusPeriodeColor()),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->state(fn (ProposalScheme $record): string => $record->periodeLabel() ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),

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
