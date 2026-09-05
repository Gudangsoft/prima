<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Role as RoleEnum;
use App\Filament\Resources\RoleResource\Pages;
use App\Support\Permissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * "Hak Akses" (grup Pengaturan): kelola peran Spatie dan izin yang melekat.
 * Hanya dapat diakses Super Admin (lihat RolePolicy).
 */
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $slug = 'hak-akses';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Hak Akses';

    protected static ?string $modelLabel = 'Peran';

    protected static ?string $pluralModelLabel = 'Peran';

    protected static ?int $navigationSort = 10;

    public static function isBuiltIn(?Role $record): bool
    {
        return $record !== null && in_array($record->name, RoleEnum::values(), true);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Peran')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Kode peran')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->rule('regex:/^[a-z][a-z0-9_]*$/')
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated(fn (string $operation): bool => $operation === 'create')
                        ->helperText('Huruf kecil tanpa spasi, mis. operator_fakultas. Tidak dapat diubah setelah dibuat.'),

                    Forms\Components\Placeholder::make('label_tampilan')
                        ->label('Label tampilan')
                        ->content(fn (?Role $record): string => $record
                            ? (RoleEnum::tryFrom($record->name)?->label() ?? ucwords(str_replace('_', ' ', $record->name)))
                            : '—'),

                    Forms\Components\Hidden::make('guard_name')->default('web'),
                ]),

            Forms\Components\Section::make('Izin')
                ->description('Centang izin yang dimiliki peran ini. Catatan: sebagian aksi alur kerja '
                    .'(persetujuan, penilaian, penetapan pendanaan, validasi luaran) ditentukan oleh peran '
                    .'secara langsung, bukan oleh izin di daftar ini.')
                ->schema([
                    Forms\Components\CheckboxList::make('permissions')
                        ->hiddenLabel()
                        ->relationship('permissions', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Permission $record): string => Permissions::label($record->name))
                        ->bulkToggleable()
                        ->searchable()
                        ->columns(2)
                        ->gridDirection('row')
                        ->disabled(fn (?Role $record): bool => $record?->name === RoleEnum::SuperAdmin->value)
                        ->helperText(fn (?Role $record): ?string => $record?->name === RoleEnum::SuperAdmin->value
                            ? 'Super Admin selalu memiliki seluruh akses (bypass), izin tidak perlu dicentang.'
                            : null),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Peran')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => RoleEnum::tryFrom($state)?->label()
                        ?? ucwords(str_replace('_', ' ', $state)))
                    ->description(fn (Role $record): string => $record->name)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Jumlah izin')
                    ->counts('permissions')
                    ->badge()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah pengguna')
                    ->counts('users')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('built_in')
                    ->label('Bawaan')
                    ->state(fn (Role $record): bool => self::isBuiltIn($record))
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Role $record): bool => ! self::isBuiltIn($record) && $record->users()->doesntExist())
                    ->before(fn (Role $record) => abort_if(self::isBuiltIn($record), 403)),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
