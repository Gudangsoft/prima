<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Memberi tahu pengusul bahwa status usulannya berubah pada tahap penting.
 */
class ProposalStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Proposal $proposal,
        public readonly ProposalStatus $to,
        public readonly ?string $catatan = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $judul = $this->proposal->judul;

        $mail = (new MailMessage)
            ->subject('Status Usulan: '.$this->to->label().' — '.$judul)
            ->greeting('Halo '.($notifiable->name ?? '').',')
            ->line($this->intro())
            ->line("**Judul:** {$judul}")
            ->line('**Status baru:** '.$this->to->label());

        if (filled($this->catatan)) {
            $mail->line('**Catatan:** '.$this->catatan);
        }

        return $mail
            ->action('Buka Usulan', url("/admin/usulan/{$this->proposal->getKey()}"))
            ->line('Terima kasih telah menggunakan SIP2M.');
    }

    private function intro(): string
    {
        return match ($this->to) {
            ProposalStatus::ApprovedLppm => 'Usulan Anda telah disetujui oleh LPPM dan akan lanjut ke tahap penilaian reviewer.',
            ProposalStatus::UnderReview => 'Usulan Anda sedang dalam proses penilaian oleh reviewer.',
            ProposalStatus::Funded => 'Selamat! Usulan Anda ditetapkan untuk didanai.',
            ProposalStatus::Rejected => 'Mohon maaf, usulan Anda tidak dapat dilanjutkan.',
            ProposalStatus::Draft => 'Usulan Anda dikembalikan untuk diperbaiki. Silakan perbarui lalu kirim ulang.',
            ProposalStatus::InProgress => 'Pelaksanaan kegiatan Anda tercatat sedang berjalan.',
            ProposalStatus::Reported => 'Laporan akhir Anda telah diterima dan menunggu validasi luaran.',
            ProposalStatus::OutputValidated => 'Seluruh luaran usulan Anda telah tervalidasi. Kegiatan dinyatakan selesai.',
            default => 'Status usulan Anda diperbarui.',
        };
    }
}
