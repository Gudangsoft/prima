<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mengirim kode OTP ke pengguna.
 *
 * Saat ini hanya lewat channel `mail` (driver `log` di dev — kode muncul di
 * storage/logs/laravel.log). Nomor HP sudah disimpan di `users.phone_number`
 * sehingga channel `sms`/WhatsApp tinggal ditambahkan di `via()` nanti tanpa
 * mengubah pemanggil.
 */
class SendOtpCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly int $ttlMinutes,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Login SIP2M')
            ->greeting('Halo '.($notifiable->name ?? '').',')
            ->line('Berikut kode verifikasi (OTP) untuk masuk ke SIP2M:')
            ->line('**'.$this->code.'**')
            ->line("Kode berlaku selama {$this->ttlMinutes} menit dan hanya untuk satu kali login.")
            ->line('Jika Anda tidak sedang mencoba masuk, abaikan email ini dan segera ubah kata sandi Anda.');
    }
}
