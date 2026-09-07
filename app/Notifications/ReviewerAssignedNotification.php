<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Proposal;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Memberi tahu reviewer bahwa ia ditugaskan menilai sebuah usulan.
 */
class ReviewerAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Proposal $proposal) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Penugasan Penilaian Usulan')
            ->body($this->proposal->judul)
            ->actions([
                Action::make('nilai')->label('Buka & Nilai')
                    ->url("/admin/usulan/{$this->proposal->getKey()}")
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Penugasan Penilaian Usulan — '.$this->proposal->judul)
            ->greeting('Halo '.($notifiable->name ?? '').',')
            ->line('Anda ditugaskan untuk menilai usulan berikut:')
            ->line("**Judul:** {$this->proposal->judul}")
            ->line('**Skema:** '.($this->proposal->scheme->nama_skema ?? '-'))
            ->action('Buka & Nilai Usulan', url("/admin/usulan/{$this->proposal->getKey()}"))
            ->line('Mohon lengkapi skor dan rekomendasi Anda sesegera mungkin.');
    }
}
