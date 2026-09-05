<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\ApprovalStatus;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Persetujuan institusi (gerbang tunggal): Admin LPPM atau Pimpinan menyetujui
 * / menolak usulan berstatus "submitted". Mencatat baris proposal_approvals
 * lalu mentransisikan status.
 */
class RecordApproval
{
    public function __invoke(Proposal $proposal, User $actor, bool $approved, ?string $catatan = null): Proposal
    {
        return DB::transaction(function () use ($proposal, $actor, $approved, $catatan): Proposal {
            ProposalApproval::create([
                'proposal_id' => $proposal->getKey(),
                'approved_by' => $actor->getKey(),
                'status' => $approved ? ApprovalStatus::Approved->value : ApprovalStatus::Rejected->value,
                'catatan' => $catatan,
            ]);

            $note = $approved
                ? trim('Disetujui LPPM. '.(string) $catatan)
                : 'Ditolak LPPM: '.($catatan ?: 'tanpa catatan');

            return app(ChangeProposalStatus::class)(
                $proposal,
                $approved ? ProposalStatus::ApprovedLppm : ProposalStatus::Rejected,
                $actor,
                $note,
            );
        });
    }
}
