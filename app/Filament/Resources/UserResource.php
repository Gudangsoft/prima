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

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')
                ->columns(2)
                ->schema([
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

                    Forms\Components\TextInput::make('nidn')
                        ->label('NIDN')
                        ->helperText('Wajib untuk dosen; kosongkan untuk non-dosen.')
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone_number')
                        ->label('Nomor HP')
                        ->tel()
                        ->maxLength(30)
                        ->rule('regex:/^[0-9+\-\s]*$/'),
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

                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable()
                    ->toggleable(),

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
