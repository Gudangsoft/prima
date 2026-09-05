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
 * Langkah kedua gerbang OTP: user memasukkan kode yang dikirim ke email-nya.
 * Kode benar -> sesi ditandai lolos & user diarahkan ke dashboard.
 */
class VerifikasiOtp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'verifikasi-otp';

    protected static ?string $title = 'Verifikasi OTP';

    protected static string $view = 'filament.pages.verifikasi-otp';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        if (! config('sip2m.otp.enabled', true)) {
            $this->redirect(route('filament.admin.pages.dashboard'));

            return;
        }

        $user = auth()->user();

        if ($this->otpAlreadyVerified($user->getKey())) {
            $this->redirect(route('filament.admin.pages.dashboard'));

            return;
        }

        if (blank($user->phone_number)) {
            $this->redirect(route('filament.admin.pages.lengkapi-profil'));

            return;
        }

        app(OtpService::class)->ensureIssuedFor($user);

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $length = app(OtpService::class)->length();

        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Kode OTP')
                    ->required()
                    ->rule('digits:'.$length)
                    ->validationMessages(['digits' => "Kode OTP harus tepat {$length} digit angka."])
                    ->autocomplete('one-time-code')
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'autofocus' => true,
                        'maxlength' => $length,
                    ])
                    ->helperText("Masukkan {$length} digit kode yang dikirim ke {$this->maskedEmail()}."),
            ])
            ->statePath('data');
    }

    public function verifikasi(): void
    {
        $code = (string) ($this->form->getState()['code'] ?? '');
        $user = auth()->user();

        $result = app(OtpService::class)->verify($user, $code);

        if (! $result['ok']) {
            Notification::make()
                ->title('Verifikasi gagal')
                ->body($result['reason'])
                ->danger()
                ->send();

            $this->reset('data');

            return;
        }

        session()->put((string) config('sip2m.otp.session_key'), $user->getKey());

        Notification::make()
            ->title('Verifikasi berhasil')
            ->body('Selamat datang di SIP2M.')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.pages.dashboard'));
    }

    public function kirimUlang(): void
    {
        $otp = app(OtpService::class);
        $user = auth()->user();

        if (! $otp->canResend($user)) {
            Notification::make()
                ->title('Tunggu sebentar')
                ->body("Anda baru bisa minta kode baru dalam {$otp->secondsUntilResend($user)} detik.")
                ->warning()
                ->send();

            return;
        }

        $otp->issueFor($user);

        Notification::make()
            ->title('Kode baru dikirim')
            ->body('Silakan cek email Anda.')
            ->success()
            ->send();
    }

    private function maskedEmail(): string
    {
        $email = (string) auth()->user()->email;

        if (! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);

        return mb_substr($name, 0, 2)
            .str_repeat('*', max(1, mb_strlen($name) - 2))
            .'@'.$domain;
    }

    private function otpAlreadyVerified(int $userId): bool
    {
        return (int) session()->get((string) config('sip2m.otp.session_key')) === $userId;
    }
}
