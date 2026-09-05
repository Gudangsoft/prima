<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\ProposalStatus;
use App\Events\ProposalStatusChanged;
use App\Notifications\ProposalStatusChangedNotification;

/**
 * Mengirim email ke pengusul saat usulannya mencapai tahap penting:
 * persetujuan/penolakan LPPM, masuk penilaian, penetapan pendanaan,
 * pengembalian revisi, dan penyelesaian (luaran tervalidasi).
 *
 * Didaftarkan eksplisit di AppServiceProvider::boot().
 */
class SendProposalStatusNotification
{
    private const NOTIFY_ON = [
        ProposalStatus::ApprovedLppm,
        ProposalStatus::UnderReview,
        ProposalStatus::Funded,
        ProposalStatus::Rejected,
        ProposalStatus::Draft,
        ProposalStatus::Reported,
        ProposalStatus::OutputValidated,
    ];

    public function handle(ProposalStatusChanged $event): void
    {
        if (! in_array($event->to, self::NOTIFY_ON, true)) {
            return;
        }

        $submitter = $event->proposal->submitter;

        $submitter?->notify(new ProposalStatusChangedNotification(
            $event->proposal,
            $event->to,
            $event->catatan,
        ));
    }
}
