<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalStatusHistory;

/**
 * Mencatat riwayat status usulan secara otomatis.
 *
 * - saat dibuat  : tulis baris riwayat awal (biasanya "draft").
 * - saat status berubah : tulis baris riwayat transisi, memakai catatan &
 *   aktor yang dititipkan lewat properti transient di model (diisi oleh
 *   ChangeProposalStatus), dengan fallback ke user yang sedang login.
 */
class ProposalObserver
{
    public function created(Proposal $proposal): void
    {
        $this->record($proposal, 'Usulan dibuat.');
    }

    public function updated(Proposal $proposal): void
    {
        if (! $proposal->wasChanged('status')) {
            return;
        }

        $this->record($proposal, $proposal->transitionNote);

        $proposal->transitionNote = null;
        $proposal->transitionActorId = null;
    }

    private function record(Proposal $proposal, ?string $catatan): void
    {
        $status = $proposal->status ?? ProposalStatus::Draft;

        ProposalStatusHistory::create([
            'proposal_id' => $proposal->getKey(),
            'changed_by' => $proposal->transitionActorId ?? auth()->id(),
            'status' => $status->value,
            'catatan' => $catatan,
        ]);
    }
}
