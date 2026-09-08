<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Role as RoleEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Http\Controllers\ImpersonationController;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationGroup = 'Data Pendukung';

    protected static ?string $navigationLabel = 'Cari Akun';

    protected static ?string $modelLabel = 'Akun';

    protected static ?string $pluralModelLabel = 'Akun Pengguna';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('avatar_path')
                        ->label('Foto profil')
                        ->avatar()
                        ->disk('public')
                        ->directory('avatars')
                        ->imageEditor()
                        ->circleCropper()
                        ->maxSize(4096)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama lengkap')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('nuptk')
                        ->label('NUPTK')
                        ->helperText('Isi bila dosen belum memiliki NIDN (mis. dosen tidak tetap).')
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('nidn')
                        ->label('NIDN')
                        ->helperText('Wajib untuk dosen tetap; kosongkan untuk non-dosen.')
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone_number')
                        ->label('Nomor HP')
                        ->tel()
                        ->maxLength(30)
                        ->rule('regex:/^[0-9+\-\s]*$/'),

                    Forms\Components\TextInput::make('jabatan')
                        ->label('Jabatan fungsional')
                        ->maxLength(100),

                    Forms\Components\Select::make('program_studi_id')
                        ->label('Program studi')
                        ->relationship('programStudi', 'nama')
                        ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->kode} — {$record->jenjang?->value} {$record->nama}")
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('unit_kerja')
                        ->label('Unit kerja lain')
                        ->helperText('Isi bila bukan program studi.')
                        ->maxLength(150),

                    Forms\Components\TextInput::make('kompetensi')
                        ->label('Kompetensi / bidang keahlian')
                        ->helperText('Dipakai untuk pemilihan reviewer.')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Data SINTA')
                ->description('Terisi otomatis lewat Import Dosen / Sinkronisasi Dosen; bisa disunting manual bila perlu.')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('sinta_id')
                        ->label('SINTA ID')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('pendidikan_terakhir')
                        ->label('Pendidikan terakhir')
                        ->maxLength(10),

                    Forms\Components\TextInput::make('sinta_score_overall_v2')
                        ->label('Skor Overall (v2)')
                        ->numeric(),

                    Forms\Components\TextInput::make('sinta_score_3yr_v2')
                        ->label('Skor 3Yr (v2)')
                        ->numeric(),

                    Forms\Components\TextInput::make('sinta_score_overall_v3')
                        ->label('Skor Overall (v3)')
                        ->numeric(),

                    Forms\Components\TextInput::make('sinta_score_3yr_v3')
                        ->label('Skor 3Yr (v3)')
                        ->numeric(),
                ]),

            Forms\Components\Section::make('Scopus & Web of Science')
                ->description('Diisi manual oleh dosen lewat Profil Saya (tidak ada koneksi API Scopus/WOS).')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('scopus_id')
                        ->label('Scopus ID')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('scopus_h_index')
                        ->label('H-Index')
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\TextInput::make('scopus_articles')
                        ->label('Articles')
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\TextInput::make('scopus_citation')
                        ->label('Citation')
                        ->numeric()
                        ->minValue(0),

                    Forms\Components\TextInput::make('wos_score')
                        ->label('WOS')
                        ->numeric()
                        ->minValue(0),
                ]),

            Forms\Components\Section::make('Akun & Peran')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label('Kata sandi')
                        ->password()
                        ->revealable()
                        ->rule(Password::default())
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->helperText(fn (string $operation): ?string => $operation === 'edit'
                            ? 'Kosongkan bila tidak ingin mengubah sandi.'
                            : null),

                    Forms\Components\Select::make('roles')
                        ->label('Peran')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(
                            fn (Model $record): string => RoleEnum::tryFrom($record->name)?->label() ?? $record->name,
                        )
                        ->helperText('Satu pengguna boleh memiliki lebih dari satu peran.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_path')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleEnum::tryFrom($state)?->label() ?? $state),

                Tables\Columns\TextColumn::make('nuptk')
                    ->label('NUPTK')
                    ->searchable(isIndividual: true)
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable(isIndividual: true)
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('programStudi.nama')
                    ->label('Program Studi')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sinta_id')
                    ->label('SINTA ID')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pendidikan_terakhir')
                    ->label('Pendidikan')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sinta_score_overall_v2')
                    ->label('Skor Overall (v2)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sinta_score_3yr_v2')
                    ->label('Skor 3Yr (v2)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sinta_score_overall_v3')
                    ->label('Skor Overall (v3)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sinta_score_3yr_v3')
                    ->label('Skor 3Yr (v3)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('scopus_id')
                    ->label('Scopus ID')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('scopus_h_index')
                    ->label('H-Index')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('scopus_articles')
                    ->label('Articles')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('scopus_citation')
                    ->label('Citation')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('wos_score')
                    ->label('WOS')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('No. HP')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('phone_number')
                    ->label('Profil OTP')
                    ->state(fn (User $record): bool => filled($record->phone_number))
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('otp_verified_at')
                    ->label('OTP terakhir lolos')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->options(RoleEnum::options())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('program_studi_id')
                    ->label('Program studi')
                    ->relationship('programStudi', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label('Login sebagai')
                    ->icon('heroicon-o-finger-print')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => "Login sebagai {$record->name}?")
                    ->modalDescription('Anda akan masuk sebagai pengguna ini. Gunakan tombol "Kembali ke akun saya" di banner atas layar untuk keluar dari mode ini.')
                    ->modalSubmitActionLabel('Login sebagai')
                    ->visible(fn (User $record): bool => Auth::user()->canImpersonate()
                        && $record->canBeImpersonated()
                        && $record->isNot(Auth::user()))
                    ->action(function (User $record) {
                        ImpersonationController::start(Auth::user(), $record);

                        Notification::make()
                            ->title("Sekarang login sebagai {$record->name}")
                            ->success()
                            ->send();

                        return redirect(Filament::getUrl());
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
