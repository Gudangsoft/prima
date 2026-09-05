<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalReview;
use App\Models\User;
use App\Notifications\ReviewerAssignedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Admin LPPM menugaskan satu atau lebih reviewer ke usulan. Bersifat menambah
 * (add-only): penugasan yang sudah pernah dibuat tidak dihapus di sini.
 *
 * Jika usulan masih "approved_lppm", penugasan pertama memindahkannya ke
 * "under_review". Reviewer yang baru ditugaskan menerima email pemberitahuan.
 *
 * @param  list<int>  $reviewerIds
 */
class AssignReviewers
{
    public function __invoke(Proposal $proposal, User $actor, array $reviewerIds): Proposal
    {
        $newlyAssignedIds = DB::transaction(function () use ($proposal, $actor, $reviewerIds): array {
            $new = [];

            foreach (array_unique($reviewerIds) as $reviewerId) {
                $review = ProposalReview::firstOrCreate([
                    'proposal_id' => $proposal->getKey(),
                    'reviewer_id' => $reviewerId,
                ]);

                if ($review->wasRecentlyCreated) {
                    $new[] = (int) $reviewerId;
                }
            }

            if ($new !== [] && $proposal->status === ProposalStatus::ApprovedLppm) {
                app(ChangeProposalStatus::class)(
                    $proposal,
                    ProposalStatus::UnderReview,
                    $actor,
                    'Reviewer ditugaskan; usulan masuk tahap penilaian.',
                );
            }

            return $new;
        });

        if ($newlyAssignedIds !== []) {
            Notification::send(
                User::whereIn('id', $newlyAssignedIds)->get(),
                new ReviewerAssignedNotification($proposal->refresh()),
            );
        }

        return $proposal->refresh();
    }
}
