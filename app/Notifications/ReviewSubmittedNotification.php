<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Proposal;
use App\Models\ProposalReview;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Memberi tahu Admin LPPM bahwa seorang reviewer telah menyelesaikan
 * penilaiannya, sehingga bisa ditindaklanjuti dengan penetapan pendanaan.
 */
class ReviewSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Proposal $proposal,
        public readonly ProposalReview $review,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Hasil Penilaian Masuk')
            ->body($this->proposal->judul)
            ->actions([
                Action::make('lihat')->label('Lihat Usulan')
                    ->url("/admin/usulan/{$this->proposal->getKey()}")
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = $this->proposal->reviews()->count();
        $selesai = $this->proposal->reviews()->whereNotNull('submitted_at')->count();

        return (new MailMessage)
            ->subject('Hasil Penilaian Masuk — '.$this->proposal->judul)
            ->greeting('Halo '.($notifiable->name ?? '').',')
            ->line('Seorang reviewer telah mengirim penilaian untuk usulan:')
            ->line("**Judul:** {$this->proposal->judul}")
            ->line('**Skor:** '.($this->review->skor ?? '-'))
            ->line('**Rekomendasi:** '.($this->review->rekomendasi?->label() ?? '-'))
            ->line("**Progres penilaian:** {$selesai} dari {$total} reviewer selesai.")
            ->action('Buka Usulan', url("/admin/usulan/{$this->proposal->getKey()}"));
    }
}
