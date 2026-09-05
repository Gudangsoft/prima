<?php

declare(strict_types=1);

namespace Tests\Feature\Proposal;

use App\Actions\Proposal\AssignReviewers;
use App\Actions\Proposal\ChangeProposalStatus;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Actions\Proposal\RecordReview;
use App\Enums\FundingStatus;
use App\Enums\ProposalStatus;
use App\Enums\ReviewRecommendation;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\ProposalStatusChangedNotification;
use App\Notifications\ReviewerAssignedNotification;
use App\Notifications\ReviewSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;

    private User $admin;

    private User $reviewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Notification::fake();

        $this->dosen = tap(User::factory()->create(), fn (User $u) => $u->assignRole('dosen'));
        $this->admin = tap(User::factory()->create(), fn (User $u) => $u->assignRole('admin_lppm'));
        $this->reviewer = tap(User::factory()->create(), fn (User $u) => $u->assignRole('reviewer'));
    }

    public function test_submitter_is_notified_once_on_approval(): void
    {
        $proposal = Proposal::factory()->status(ProposalStatus::Submitted)->forDosen($this->dosen)->create();

        app(RecordApproval::class)($proposal, $this->admin, true, 'ok');

        Notification::assertSentToTimes($this->dosen, ProposalStatusChangedNotification::class, 1);
    }

    public function test_submitter_is_notified_on_funding_decision(): void
    {
        $proposal = Proposal::factory()->status(ProposalStatus::UnderReview)->forDosen($this->dosen)->create();

        app(RecordFundingDecision::class)($proposal, $this->admin, FundingStatus::Didanai, 10_000_000);

        Notification::assertSentTo($this->dosen, ProposalStatusChangedNotification::class,
            fn (ProposalStatusChangedNotification $n) => $n->to === ProposalStatus::Funded);
    }

    public function test_no_email_for_unimportant_transition(): void
    {
        $proposal = Proposal::factory()->status(ProposalStatus::Funded)->forDosen($this->dosen)->create();

        // funded -> in_progress tidak termasuk daftar "penting".
        app(ChangeProposalStatus::class)($proposal, ProposalStatus::InProgress, $this->admin);

        Notification::assertNotSentTo($this->dosen, ProposalStatusChangedNotification::class);
    }

    public function test_reviewer_is_notified_when_assigned(): void
    {
        $proposal = Proposal::factory()->status(ProposalStatus::ApprovedLppm)->forDosen($this->dosen)->create();

        app(AssignReviewers::class)($proposal, $this->admin, [$this->reviewer->id]);

        Notification::assertSentTo($this->reviewer, ReviewerAssignedNotification::class);
        // Re-assign tidak mengirim ulang.
        app(AssignReviewers::class)($proposal->fresh(), $this->admin, [$this->reviewer->id]);
        Notification::assertSentToTimes($this->reviewer, ReviewerAssignedNotification::class, 1);
    }

    public function test_admin_is_notified_when_review_submitted(): void
    {
        $proposal = Proposal::factory()->status(ProposalStatus::ApprovedLppm)->forDosen($this->dosen)->create();
        app(AssignReviewers::class)($proposal, $this->admin, [$this->reviewer->id]);

        app(RecordReview::class)($proposal->fresh(), $this->reviewer, 90, ReviewRecommendation::Danai, 'kuat');

        Notification::assertSentTo($this->admin, ReviewSubmittedNotification::class);
    }

    public function test_notifications_are_queued(): void
    {
        $this->assertContains(
            ShouldQueue::class,
            class_implements(ProposalStatusChangedNotification::class),
        );
    }
}
