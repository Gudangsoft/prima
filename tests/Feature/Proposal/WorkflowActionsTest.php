<?php

declare(strict_types=1);

namespace Tests\Feature\Proposal;

use App\Actions\Proposal\AssignReviewers;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Actions\Proposal\RecordOutputValidation;
use App\Actions\Proposal\RecordReview;
use App\Actions\Proposal\SubmitMonitoringReport;
use App\Enums\FundingStatus;
use App\Enums\OutputType;
use App\Enums\OutputValidationStatus;
use App\Enums\ProposalStatus;
use App\Enums\ReportType;
use App\Enums\ReviewRecommendation;
use App\Models\Output;
use App\Models\Proposal;
use App\Models\ProposalReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;

    private User $admin;

    private User $reviewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $this->dosen = tap(User::factory()->create(), fn (User $u) => $u->assignRole('dosen'));
        $this->admin = tap(User::factory()->create(), fn (User $u) => $u->assignRole('admin_lppm'));
        $this->reviewer = tap(User::factory()->create(), fn (User $u) => $u->assignRole('reviewer'));
    }

    private function proposalAt(ProposalStatus $status): Proposal
    {
        return Proposal::factory()->status($status)->forDosen($this->dosen)->create();
    }

    private function otpVerified(User $user): self
    {
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $this;
    }

    public function test_view_page_with_all_relation_managers_renders_for_each_role(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::UnderReview);
        ProposalReview::create(['proposal_id' => $proposal->id, 'reviewer_id' => $this->reviewer->id]);

        $url = "/admin/usulan/{$proposal->id}";

        $this->otpVerified($this->admin)->get($url)->assertOk();
        $this->otpVerified($this->dosen)->get($url)->assertOk();
        $this->otpVerified($this->reviewer)->get($url)->assertOk();
    }

    public function test_approval_records_row_and_transitions(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::Submitted);

        app(RecordApproval::class)($proposal, $this->admin, true, 'lengkap');

        $this->assertSame(ProposalStatus::ApprovedLppm, $proposal->fresh()->status);
        $this->assertDatabaseHas('proposal_approvals', [
            'proposal_id' => $proposal->id,
            'approved_by' => $this->admin->id,
            'status' => 'approved',
        ]);
    }

    public function test_rejection_at_approval_stage(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::Submitted);

        app(RecordApproval::class)($proposal, $this->admin, false, 'tidak sesuai fokus');

        $this->assertSame(ProposalStatus::Rejected, $proposal->fresh()->status);
        $this->assertTrue($proposal->fresh()->status->isTerminal());
    }

    public function test_assigning_reviewers_moves_proposal_into_review(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::ApprovedLppm);
        $reviewer2 = tap(User::factory()->create(), fn (User $u) => $u->assignRole('reviewer'));

        app(AssignReviewers::class)($proposal, $this->admin, [$this->reviewer->id, $reviewer2->id]);

        $this->assertSame(ProposalStatus::UnderReview, $proposal->fresh()->status);
        $this->assertDatabaseCount('proposal_reviews', 2);
    }

    public function test_reviewer_records_score_and_recommendation(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::ApprovedLppm);
        app(AssignReviewers::class)($proposal, $this->admin, [$this->reviewer->id]);

        $review = app(RecordReview::class)(
            $proposal->fresh(),
            $this->reviewer,
            88,
            ReviewRecommendation::Danai,
            'kuat secara metodologis',
        );

        $this->assertSame(88, $review->skor);
        $this->assertSame(ReviewRecommendation::Danai, $review->rekomendasi);
        $this->assertNotNull($review->submitted_at);
        $this->assertTrue($review->isSubmitted());
    }

    public function test_funding_decision_funded_and_not_funded_paths(): void
    {
        $funded = $this->proposalAt(ProposalStatus::UnderReview);
        app(RecordFundingDecision::class)($funded, $this->admin, FundingStatus::Didanai, 30_000_000, 'SK-9/2026');

        $this->assertSame(ProposalStatus::Funded, $funded->fresh()->status);
        $this->assertDatabaseHas('funding_decisions', [
            'proposal_id' => $funded->id,
            'status_danai' => 'didanai',
            'jumlah_dana' => '30000000.00',
        ]);

        $notFunded = $this->proposalAt(ProposalStatus::UnderReview);
        app(RecordFundingDecision::class)($notFunded, $this->admin, FundingStatus::TidakDidanai);

        $this->assertSame(ProposalStatus::Rejected, $notFunded->fresh()->status);
        $this->assertEquals(0, $notFunded->fundingDecision->jumlah_dana);
    }

    public function test_monitoring_reports_drive_progress_and_reported(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::Funded);

        app(SubmitMonitoringReport::class)($proposal, $this->dosen, ReportType::Kemajuan, 'reports/k.pdf', 'progres 50%');
        $this->assertSame(ProposalStatus::InProgress, $proposal->fresh()->status);

        app(SubmitMonitoringReport::class)($proposal->fresh(), $this->dosen, ReportType::Akhir, 'reports/a.pdf', 'selesai');
        $this->assertSame(ProposalStatus::Reported, $proposal->fresh()->status);

        $this->assertDatabaseCount('monitoring_reports', 2);
    }

    public function test_output_validation_completes_the_lifecycle(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::Reported);

        $o1 = Output::create([
            'proposal_id' => $proposal->id,
            'jenis_luaran' => OutputType::Publikasi->value,
            'status_validasi' => OutputValidationStatus::Pending->value,
        ]);
        $o2 = Output::create([
            'proposal_id' => $proposal->id,
            'jenis_luaran' => OutputType::Hki->value,
            'status_validasi' => OutputValidationStatus::Pending->value,
        ]);

        app(RecordOutputValidation::class)($o1, $this->admin, OutputValidationStatus::Valid);
        $this->assertSame(ProposalStatus::Reported, $proposal->fresh()->status, 'belum semua luaran valid');

        app(RecordOutputValidation::class)($o2, $this->admin, OutputValidationStatus::Valid);
        $this->assertSame(ProposalStatus::OutputValidated, $proposal->fresh()->status);
    }

    public function test_full_history_is_recorded_for_every_transition(): void
    {
        $proposal = $this->proposalAt(ProposalStatus::Submitted);

        app(RecordApproval::class)($proposal, $this->admin, true);
        app(AssignReviewers::class)($proposal->fresh(), $this->admin, [$this->reviewer->id]);
        app(RecordFundingDecision::class)($proposal->fresh(), $this->admin, FundingStatus::Didanai, 10_000_000);
        app(SubmitMonitoringReport::class)($proposal->fresh(), $this->dosen, ReportType::Akhir, 'reports/a.pdf');

        $statuses = $proposal->statusHistories()->pluck('status')->map->value->all();

        $this->assertContains('approved_lppm', $statuses);
        $this->assertContains('under_review', $statuses);
        $this->assertContains('funded', $statuses);
        $this->assertContains('in_progress', $statuses);
        $this->assertContains('reported', $statuses);
    }

    public function test_download_route_enforces_proposal_policy(): void
    {
        $ownProposal = Proposal::factory()->withFile()->forDosen($this->dosen)->create();
        $foreignDosen = tap(User::factory()->create(), fn (User $u) => $u->assignRole('dosen'));
        $foreignProposal = Proposal::factory()->withFile()->forDosen($foreignDosen)->create();

        $this->actingAs($this->dosen)
            ->withSession([config('sip2m.otp.session_key') => $this->dosen->getKey()]);

        // Berkas tidak ada di storage -> 404 (bukan 403) untuk usulan sendiri.
        $this->get(route('download.proposal', $ownProposal))->assertNotFound();

        // Usulan orang lain -> 403 dari policy sebelum menyentuh storage.
        $this->get(route('download.proposal', $foreignProposal))->assertForbidden();
    }
}
