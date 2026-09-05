<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ProposalStatus;
use PHPUnit\Framework\TestCase;

class ProposalStatusTest extends TestCase
{
    public function test_happy_path_transitions_are_allowed(): void
    {
        $this->assertTrue(ProposalStatus::Draft->canTransitionTo(ProposalStatus::Submitted));
        $this->assertTrue(ProposalStatus::Submitted->canTransitionTo(ProposalStatus::ApprovedLppm));
        $this->assertTrue(ProposalStatus::ApprovedLppm->canTransitionTo(ProposalStatus::UnderReview));
        $this->assertTrue(ProposalStatus::UnderReview->canTransitionTo(ProposalStatus::Funded));
        $this->assertTrue(ProposalStatus::Funded->canTransitionTo(ProposalStatus::InProgress));
        $this->assertTrue(ProposalStatus::InProgress->canTransitionTo(ProposalStatus::Reported));
        $this->assertTrue(ProposalStatus::Reported->canTransitionTo(ProposalStatus::OutputValidated));
    }

    public function test_rejection_branches_are_allowed(): void
    {
        $this->assertTrue(ProposalStatus::Submitted->canTransitionTo(ProposalStatus::Rejected));
        $this->assertTrue(ProposalStatus::ApprovedLppm->canTransitionTo(ProposalStatus::Rejected));
        $this->assertTrue(ProposalStatus::UnderReview->canTransitionTo(ProposalStatus::Rejected));
    }

    public function test_return_for_revision_branches_are_allowed(): void
    {
        $this->assertTrue(ProposalStatus::Submitted->canTransitionTo(ProposalStatus::Draft));
        $this->assertTrue(ProposalStatus::Reported->canTransitionTo(ProposalStatus::InProgress));
    }

    public function test_illegal_jumps_are_blocked(): void
    {
        $this->assertFalse(ProposalStatus::Draft->canTransitionTo(ProposalStatus::ApprovedLppm));
        $this->assertFalse(ProposalStatus::Draft->canTransitionTo(ProposalStatus::Funded));
        $this->assertFalse(ProposalStatus::Submitted->canTransitionTo(ProposalStatus::UnderReview));
        $this->assertFalse(ProposalStatus::ApprovedLppm->canTransitionTo(ProposalStatus::Funded));
        $this->assertFalse(ProposalStatus::Funded->canTransitionTo(ProposalStatus::Reported));
    }

    public function test_terminal_states_have_no_transitions(): void
    {
        $this->assertTrue(ProposalStatus::Rejected->isTerminal());
        $this->assertTrue(ProposalStatus::OutputValidated->isTerminal());
        $this->assertSame([], ProposalStatus::Rejected->allowedTransitions());
    }

    public function test_every_status_has_a_label_and_color(): void
    {
        foreach (ProposalStatus::cases() as $status) {
            $this->assertNotEmpty($status->label());
            $this->assertNotEmpty($status->color());
        }
    }
}
