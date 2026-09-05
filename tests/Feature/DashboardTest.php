<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\MyProposalsOverview;
use App\Filament\Widgets\ProposalsByStatusChart;
use App\Filament\Widgets\ProposalStatsOverview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_dashboard_renders_for_every_role(): void
    {
        foreach (['admin_lppm', 'pimpinan', 'reviewer', 'dosen', 'super_admin'] as $role) {
            $this->actingOtpVerified($role);
            $this->get('/admin')->assertOk();
        }
    }

    public function test_recap_widgets_are_visible_only_to_oversight_roles(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $this->assertTrue(ProposalStatsOverview::canView());
        $this->assertTrue(ProposalsByStatusChart::canView());
        $this->assertFalse(MyProposalsOverview::canView());

        $this->actingOtpVerified('dosen');
        $this->assertFalse(ProposalStatsOverview::canView());
        $this->assertTrue(MyProposalsOverview::canView());

        $this->actingOtpVerified('reviewer');
        $this->assertFalse(ProposalStatsOverview::canView());
        $this->assertFalse(MyProposalsOverview::canView());
    }
}
