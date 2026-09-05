<?php

declare(strict_types=1);

namespace Tests\Feature\Proposal;

use App\Actions\Proposal\ChangeProposalStatus;
use App\Enums\ProposalStatus;
use App\Exceptions\InvalidProposalTransition;
use App\Models\Proposal;
use App\Models\ProposalReview;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function user(string $role): User
    {
        $u = User::factory()->create(['phone_number' => '081234567890']);
        $u->assignRole($role);

        return $u;
    }

    private function actAs(User $user): self
    {
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $this;
    }

    public function test_observer_logs_initial_history_on_create(): void
    {
        $proposal = Proposal::factory()->create();

        $this->assertDatabaseCount('proposal_status_histories', 1);
        $this->assertSame(ProposalStatus::Draft, $proposal->statusHistories()->first()->status);
    }

    public function test_change_status_action_enforces_state_machine_and_logs(): void
    {
        $dosen = $this->user('dosen');
        $proposal = Proposal::factory()->withFile()->forDosen($dosen)->create();

        app(ChangeProposalStatus::class)($proposal, ProposalStatus::Submitted, $dosen, 'kirim');

        $this->assertSame(ProposalStatus::Submitted, $proposal->fresh()->status);
        $this->assertDatabaseHas('proposal_status_histories', [
            'proposal_id' => $proposal->id,
            'status' => 'submitted',
            'changed_by' => $dosen->id,
            'catatan' => 'kirim',
        ]);

        $this->expectException(InvalidProposalTransition::class);
        app(ChangeProposalStatus::class)($proposal->fresh(), ProposalStatus::OutputValidated, $dosen);
    }

    public function test_submit_policy_requires_ownership_draft_and_file(): void
    {
        $dosen = $this->user('dosen');
        $other = $this->user('dosen');

        $withoutFile = Proposal::factory()->forDosen($dosen)->create(['status' => ProposalStatus::Draft->value]);
        $ready = Proposal::factory()->withFile()->forDosen($dosen)->create(['status' => ProposalStatus::Draft->value]);
        $foreign = Proposal::factory()->withFile()->forDosen($other)->create(['status' => ProposalStatus::Draft->value]);

        $this->assertFalse($dosen->can('submit', $withoutFile), 'tanpa berkas tidak boleh submit');
        $this->assertTrue($dosen->can('submit', $ready));
        $this->assertFalse($dosen->can('submit', $foreign), 'bukan pemilik');
    }

    public function test_dosen_cannot_edit_after_submitted(): void
    {
        $dosen = $this->user('dosen');
        $proposal = Proposal::factory()->status(ProposalStatus::Submitted)->forDosen($dosen)->create();

        $this->assertFalse($dosen->can('update', $proposal));
        $this->assertFalse($dosen->can('delete', $proposal));
    }

    public function test_list_query_is_scoped_per_role(): void
    {
        $dosen = $this->user('dosen');
        $reviewer = $this->user('reviewer');

        $own = Proposal::factory()->forDosen($dosen)->create(['judul' => 'Usulan Milik Dosen A']);
        $foreign = Proposal::factory()->create(['judul' => 'Usulan Orang Lain']);
        $assigned = Proposal::factory()->status(ProposalStatus::UnderReview)->create(['judul' => 'Usulan Ditugaskan']);
        ProposalReview::create(['proposal_id' => $assigned->id, 'reviewer_id' => $reviewer->id]);

        // Dosen: hanya miliknya.
        $this->actAs($dosen)->get('/admin/usulan')
            ->assertOk()
            ->assertSee('Usulan Milik Dosen A')
            ->assertDontSee('Usulan Orang Lain');

        // Reviewer: hanya yang ditugaskan.
        $this->actAs($reviewer)->get('/admin/usulan')
            ->assertOk()
            ->assertSee('Usulan Ditugaskan')
            ->assertDontSee('Usulan Milik Dosen A');

        // Admin LPPM: semua.
        $admin = $this->user('admin_lppm');
        $this->actAs($admin)->get('/admin/usulan')
            ->assertOk()
            ->assertSee('Usulan Milik Dosen A')
            ->assertSee('Usulan Orang Lain');
    }

    public function test_only_dosen_can_open_create_page(): void
    {
        $this->actAs($this->user('dosen'))->get('/admin/usulan/create')->assertOk();
        $this->actAs($this->user('admin_lppm'))->get('/admin/usulan/create')->assertForbidden();
        $this->actAs($this->user('reviewer'))->get('/admin/usulan/create')->assertForbidden();
    }

    public function test_scheme_in_use_cannot_be_deleted(): void
    {
        $admin = $this->user('admin_lppm');
        $scheme = ProposalScheme::factory()->create();
        Proposal::factory()->forScheme($scheme)->create();

        $this->assertFalse($admin->can('delete', $scheme));
    }
}
