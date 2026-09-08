<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

/**
 * Login dengan email ATAU NIDN/NUPTK (dosen umumnya hafal NIDN/NUPTK, bukan
 * email placeholder hasil impor). Akun tanpa NIDN/NUPTK (admin LPPM,
 * pimpinan, reviewer, super admin) tetap login pakai email seperti biasa.
 */
class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email / NIDN / NUPTK')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim((string) $data['email']);

        $email = str_contains($login, '@')
            ? $login
            : (User::where('nidn', $login)->orWhere('nuptk', $login)->value('email') ?? $login);

        return [
            'email' => $email,
            'password' => $data['password'],
        ];
    }
}
