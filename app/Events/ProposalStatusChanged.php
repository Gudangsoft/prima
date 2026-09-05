<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dipancarkan oleh ChangeProposalStatus setiap kali status usulan berpindah.
 * Dipakai untuk memicu notifikasi email (Fase 9).
 */
class ProposalStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Proposal $proposal,
        public readonly ProposalStatus $from,
        public readonly ProposalStatus $to,
        public readonly ?int $actorId = null,
        public readonly ?string $catatan = null,
    ) {}
}
