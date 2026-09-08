<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use App\Enums\Role as RoleEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

/**
 * Halaman Profil lengkap: foto (crop lingkaran), data diri, kontak, dan
 * ganti kata sandi. Foto juga tampil di menu pengguna pojok kanan atas.
 */
class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Profil Saya';

    protected static string $view = 'filament.pages.profil';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Foto Profil')
                    ->description('Foto akan tampil di menu pengguna dan daftar. Gambar dipotong berbentuk lingkaran.')
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->hiddenLabel()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(4096),
                    ]),

                Section::make('Data Diri')
                    ->columns(2)
                    ->schema([
                        $this->getNameFormComponent(),
                        TextInput::make('nuptk')
                            ->label('NUPTK')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nidn')
                            ->label('NIDN')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        TextInput::make('jabatan')
                            ->label('Jabatan fungsional')
                            ->placeholder('mis. Lektor Kepala')
                            ->maxLength(100),
                        TextInput::make('unit_kerja')
                            ->label('Program studi / unit kerja')
                            ->maxLength(150),
                        TextInput::make('kompetensi')
                            ->label('Kompetensi / bidang keahlian')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('bio')
                            ->label('Tentang saya')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kontak & Akun')
                    ->columns(2)
                    ->schema([
                        $this->getEmailFormComponent(),
                        TextInput::make('phone_number')
                            ->label('Nomor HP / WhatsApp')
                            ->tel()
                            ->maxLength(30)
                            ->rule('regex:/^[0-9+\-\s]*$/'),
                        TextInput::make('telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(30),
                    ]),

                Section::make('Scopus & Web of Science')
                    ->description('Sistem ini tidak terhubung ke API Scopus/WOS — isi sendiri sesuai profil Scopus/WOS Anda.')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('scopus_id')
                            ->label('Scopus ID')
                            ->maxLength(50),
                        TextInput::make('scopus_h_index')
                            ->label('H-Index')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('scopus_articles')
                            ->label('Articles')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('scopus_citation')
                            ->label('Citation')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('wos_score')
                            ->label('WOS')
                            ->numeric()
                            ->minValue(0),
                    ]),

                Section::make('Ubah Kata Sandi')
                    ->description('Kosongkan bila tidak ingin mengganti kata sandi.')
                    ->columns(2)
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    /** Data untuk kartu ringkasan di atas form. */
    public function getHeaderData(): array
    {
        $user = $this->getUser();

        return [
            'avatarUrl' => $user->getFilamentAvatarUrl(),
            'inisial' => collect(explode(' ', trim((string) $user->name)))
                ->filter()->take(2)
                ->map(fn (string $w): string => mb_strtoupper(mb_substr($w, 0, 1)))
                ->implode('') ?: 'U',
            'nama' => $user->name,
            'email' => $user->email,
            'jabatan' => $user->jabatan,
            'unitKerja' => $user->unit_kerja,
            'roles' => $user->getRoleNames()
                ->map(fn (string $r): string => RoleEnum::tryFrom($r)?->label() ?? $r)
                ->all(),
            'bergabung' => $user->created_at?->translatedFormat('F Y'),
            'emailVerified' => $user->email_verified_at !== null,
            'otpProfil' => filled($user->phone_number),
        ];
    }
}
