<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Role as RoleEnum;
use App\Filament\Pages\Concerns\OversightTablePage;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * "Daftar Reviewer" (grup Pengelolaan Reviewer): daftar reviewer beserta
 * kontak, kompetensi, dan beban penugasan/penilaian.
 */
class DaftarReviewer extends OversightTablePage
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Pengelolaan Reviewer';

    protected static ?string $navigationLabel = 'Daftar Reviewer';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Daftar Reviewer';

    public function getSubheading(): ?string
    {
        return 'Reviewer penilai usulan penelitian & pengabdian kepada masyarakat';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Reviewer->value))
                    ->withCount([
                        'reviewAssignments',
                        'reviewAssignments as review_selesai_count' => fn ($q) => $q->whereNotNull('submitted_at'),
                    ]),
            )
            ->columns([
                Tables\Columns\TextColumn::make('no')->label('No')->rowIndex(),

                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->formatStateUsing(fn (string $state): string => mb_strtoupper($state))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Kontak')
                    ->icon('heroicon-m-phone')
                    ->placeholder('—')
                    ->description(fn (User $record): string => $record->email, position: 'below'),

                Tables\Columns\TextColumn::make('kompetensi')
                    ->label('Kompetensi')
                    ->wrap()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('review_assignments_count')
                    ->label('Ditugaskan')->alignCenter()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('review_selesai_count')
                    ->label('Selesai Dinilai')->alignCenter()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('beban_aktif')
                    ->label('Beban Aktif')
                    ->badge()->color('warning')->alignCenter()
                    ->getStateUsing(fn (User $record): int => max(0, ($record->review_assignments_count ?? 0) - ($record->review_selesai_count ?? 0)))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('tambah')
                    ->label('Tambah Reviewer')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Tambah Reviewer')
                    ->form([
                        Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->required()
                            ->unique('users', 'email'),
                        Forms\Components\TextInput::make('nidn')->label('NIDN')->maxLength(20)
                            ->unique('users', 'nidn'),
                        Forms\Components\TextInput::make('phone_number')->label('Nomor HP')->tel()->maxLength(30),
                        Forms\Components\TextInput::make('kompetensi')->label('Kompetensi / bidang keahlian')->maxLength(255),
                        Forms\Components\TextInput::make('password')->label('Kata sandi')->password()->revealable()
                            ->required()->rule(Password::default()),
                    ])
                    ->action(function (array $data): void {
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'nidn' => $data['nidn'] ?: null,
                            'phone_number' => $data['phone_number'] ?: null,
                            'kompetensi' => $data['kompetensi'] ?: null,
                            'password' => Hash::make($data['password']),
                            'email_verified_at' => now(),
                        ]);
                        $user->assignRole(RoleEnum::Reviewer->value);

                        Notification::make()->title('Reviewer ditambahkan')->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Ubah')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('name');
    }
}
