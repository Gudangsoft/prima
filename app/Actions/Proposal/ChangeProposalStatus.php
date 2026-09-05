<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\ProposalStatus;
use App\Events\ProposalStatusChanged;
use App\Exceptions\InvalidProposalTransition;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu untuk mengubah status usulan.
 *
 * Memvalidasi transisi terhadap state machine (ProposalStatus::map()), lalu
 * menyimpan. Pencatatan ke proposal_status_histories dilakukan otomatis oleh
 * ProposalObserver menggunakan catatan & aktor yang dititipkan di sini.
 */
class ChangeProposalStatus
{
    /**
     * @throws InvalidProposalTransition
     */
    public function __invoke(
        Proposal $proposal,
        ProposalStatus $to,
        ?User $actor = null,
        ?string $catatan = null,
    ): Proposal {
        $from = $proposal->status;

        if ($from === $to) {
            return $proposal;
        }

        if (! $from->canTransitionTo($to)) {
            throw InvalidProposalTransition::between($from, $to);
        }

        $proposal = DB::transaction(function () use ($proposal, $to, $actor, $catatan): Proposal {
            $proposal->transitionNote = $catatan;
            $proposal->transitionActorId = $actor?->getKey();
            $proposal->status = $to;
            $proposal->save();

            return $proposal->refresh();
        });

        ProposalStatusChanged::dispatch($proposal, $from, $to, $actor?->getKey(), $catatan);

        return $proposal;
    }
}
