<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ProposalStatus;
use DomainException;

class InvalidProposalTransition extends DomainException
{
    public static function between(ProposalStatus $from, ProposalStatus $to): self
    {
        return new self(sprintf(
            'Transisi status usulan tidak valid: "%s" -> "%s".',
            $from->label(),
            $to->label(),
        ));
    }
}
