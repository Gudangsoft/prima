<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Support\Settings;
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

        Announcement::create([
            'judul' => 'Pembukaan Usulan Tahun Anggaran 2026',
            'isi' => '<p>Silakan ajukan usulan.</p>',
            'tanggal_terbit' => now()->subDay(),
            'terbit' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Alur Layanan')
            ->assertSee('Pengumuman')
            ->assertSee('Masuk ke Sistem')
            ->assertSeeText('Pembukaan Usulan Tahun Anggaran 2026');
    }

    public function test_landing_page_shows_live_counts_and_branding(): void
    {
        Proposal::factory()->count(5)->create();
        Settings::set('app_name', 'PORTAL-RISET');

        $response = $this->get('/')->assertOk();
        $response->assertSee('Total Usulan');
        $response->assertSee('Dana Tersalur');
        $response->assertSee('PORTAL-RISET');
    }

    public function test_draft_announcement_is_hidden_from_public(): void
    {
        Announcement::create([
            'judul' => 'Draf Rahasia',
            'isi' => '<p>x</p>',
            'tanggal_terbit' => now(),
            'terbit' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('Draf Rahasia');
    }
}
