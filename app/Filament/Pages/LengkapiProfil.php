<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Otp\OtpService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Langkah pertama gerbang OTP: user yang belum mengisi nomor HP aktif
 * wajib melengkapinya sebelum kode OTP bisa dikirim.
 */
class LengkapiProfil extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'lengkapi-profil';

    protected static ?string $title = 'Lengkapi Profil';

    protected static string $view = 'filament.pages.lengkapi-profil';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        if ($this->otpAlreadyVerified($user->getKey())) {
            $this->redirect(route('filament.admin.pages.dashboard'));

            return;
        }

        if (filled($user->phone_number)) {
            $this->redirect(route('filament.admin.pages.verifikasi-otp'));

            return;
        }

        $this->form->fill([
            'email' => $user->email,
            'phone_number' => $user->phone_number,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Email aktif')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->helperText('Kode OTP akan dikirim ke alamat ini.'),
                TextInput::make('phone_number')
                    ->label('Nomor HP aktif')
                    ->tel()
                    ->required()
                    ->minLength(8)
                    ->maxLength(30)
                    ->rule('regex:/^[0-9+\-\s]+$/')
                    ->helperText('Contoh: 081234567890. Dipakai untuk verifikasi dan kontak LPPM.'),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $user->forceFill([
            'email' => $data['email'],
            'phone_number' => preg_replace('/\s+/', '', $data['phone_number']),
        ])->save();

        app(OtpService::class)->issueFor($user);

        Notification::make()
            ->title('Kode OTP dikirim')
            ->body('Silakan cek email Anda dan masukkan kode verifikasi.')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.pages.verifikasi-otp'));
    }

    private function otpAlreadyVerified(int $userId): bool
    {
        return (int) session()->get((string) config('sip2m.otp.session_key')) === $userId;
    }
}
