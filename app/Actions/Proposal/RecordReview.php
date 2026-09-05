<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\ReviewRecommendation;
use App\Enums\Role;
use App\Models\Proposal;
use App\Models\ProposalReview;
use App\Models\User;
use App\Notifications\ReviewSubmittedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Reviewer mengisi hasil penilaian atas usulan yang ditugaskan kepadanya.
 * Tidak mengubah status usulan (penetapan pendanaan dilakukan Admin LPPM),
 * tetapi memberi tahu Admin LPPM lewat email.
 */
class RecordReview
{
    public function __invoke(
        Proposal $proposal,
        User $reviewer,
        int $skor,
        ReviewRecommendation $rekomendasi,
        ?string $catatan = null,
    ): ProposalReview {
        $review = ProposalReview::query()
            ->where('proposal_id', $proposal->getKey())
            ->where('reviewer_id', $reviewer->getKey())
            ->firstOrFail();

        $review->update([
            'skor' => max(0, min(100, $skor)),
            'rekomendasi' => $rekomendasi->value,
            'catatan' => $catatan,
            'submitted_at' => now(),
        ]);

        Notification::send(
            User::role(Role::AdminLppm->value)->get(),
            new ReviewSubmittedNotification($proposal->refresh(), $review),
        );

        return $review;
    }
}
