<?php

declare(strict_types=1);

namespace Tests\Feature\Reviewer;

use App\Enums\ProposalStatus;
use App\Filament\Pages\PlottingReviewer;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlottingReviewerTest extends TestCase
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

    public function test_page_shows_bima_style_filters(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $this->get(PlottingReviewer::getUrl())
            ->assertOk()
            ->assertSeeText('Tahun Pelaksanaan')
            ->assertSeeText('Tahapan')
            ->assertSeeText('Kegiatan');
    }

    public function test_kategori_filter_narrows_to_matching_scheme(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $penelitian = ProposalScheme::factory()->penelitian()->create();
        $pengabdian = ProposalScheme::factory()->pengabdian()->create();

        $usulanPenelitian = Proposal::factory()
            ->status(ProposalStatus::UnderReview)
            ->create(['scheme_id' => $penelitian->id, 'judul' => 'Usulan Penelitian Uji']);
        $usulanPengabdian = Proposal::factory()
            ->status(ProposalStatus::UnderReview)
            ->create(['scheme_id' => $pengabdian->id, 'judul' => 'Usulan Pengabdian Uji']);

        Livewire::test(PlottingReviewer::class)
            ->assertSee('Usulan Penelitian Uji')
            ->assertSee('Usulan Pengabdian Uji')
            ->filterTable('kategori', 'penelitian')
            ->assertSee('Usulan Penelitian Uji')
            ->assertDontSee('Usulan Pengabdian Uji');
    }
}
