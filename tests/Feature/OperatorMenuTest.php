<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\Output;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorMenuTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/admin/profil-lembaga',
        '/admin/monitoring-pelaksanaan',
        '/admin/validasi-luaran',
        '/admin/plotting-reviewer',
        '/admin/daftar-reviewer',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function actingOtpVerified(string $role): User
    {
        $user = User::factory()->create(['phone_number' => '081234567890']);
        $user->assignRole($role);
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $user;
    }

    public function test_all_operator_pages_render_for_admin_lppm(): void
    {
        $this->actingOtpVerified('admin_lppm');

        Proposal::factory()->status(ProposalStatus::UnderReview)->create();
        $reported = Proposal::factory()->status(ProposalStatus::Reported)->create();
        Output::create([
            'proposal_id' => $reported->id,
            'jenis_luaran' => 'publikasi',
            'status_validasi' => 'pending',
        ]);

        foreach (self::PAGES as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_operator_pages_are_forbidden_for_dosen_and_reviewer(): void
    {
        foreach (['dosen', 'reviewer'] as $role) {
            $this->actingOtpVerified($role);
            foreach (self::PAGES as $url) {
                $this->get($url)->assertForbidden();
            }
        }
    }

    public function test_pimpinan_can_view_operator_pages(): void
    {
        $this->actingOtpVerified('pimpinan');

        foreach (self::PAGES as $url) {
            $this->get($url)->assertOk();
        }
    }
}
