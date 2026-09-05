<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_landing_page_renders_for_guests(): void
    {
        Proposal::factory()->count(3)->create();
        Proposal::factory()->status(ProposalStatus::Funded)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('SIP2M')
            ->assertSee('Alur Layanan')
            ->assertSee('Pengumuman')
            ->assertSee('Masuk ke Sistem')
            ->assertSeeText('Pembukaan Usulan Tahun Anggaran 2026');
    }

    public function test_landing_page_shows_live_counts(): void
    {
        Proposal::factory()->count(5)->create();

        $response = $this->get('/')->assertOk();
        $response->assertSee('Total Usulan');
        $response->assertSee('Dana Tersalur');
    }
}
