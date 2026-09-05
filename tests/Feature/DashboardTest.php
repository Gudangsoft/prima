<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\AnnouncementsWidget;
use App\Filament\Widgets\DashboardIdentity;
use App\Filament\Widgets\MonitoringPelaksanaanCard;
use App\Filament\Widgets\MonitoringUsulanCard;
use App\Filament\Widgets\ProfilLembagaCard;
use App\Filament\Widgets\ProfilPimpinanCard;
use App\Filament\Widgets\ProfilSayaCard;
use App\Filament\Widgets\ProposalsBySchemeChart;
use App\Filament\Widgets\ProposalsByStatusChart;
use App\Filament\Widgets\ProposalsByYearChart;
use App\Filament\Widgets\RiwayatUsulanCard;
use App\Filament\Widgets\UsulanSayaStats;
use App\Filament\Widgets\UsulanSayaYearChart;
use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\ProposalSchemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        Proposal::factory()->count(4)->create();

        foreach (['admin_lppm', 'pimpinan', 'reviewer', 'dosen', 'super_admin'] as $role) {
            $this->actingOtpVerified($role);
            $this->get('/admin')->assertOk();
        }
    }

    public function test_oversight_only_widgets_are_gated(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $this->assertTrue(MonitoringPelaksanaanCard::canView());
        $this->assertTrue(ProposalsByStatusChart::canView());

        $this->assertTrue(MonitoringUsulanCard::canView());
        $this->assertTrue(ProfilLembagaCard::canView());

        $this->actingOtpVerified('dosen');
        $this->assertFalse(MonitoringPelaksanaanCard::canView());
        $this->assertFalse(ProposalsByStatusChart::canView());

        // Kartu pengawas disembunyikan dari dosen; dosen memakai widget khusus.
        $this->assertFalse(MonitoringUsulanCard::canView());
        $this->assertFalse(ProfilLembagaCard::canView());
        $this->assertTrue(ProfilSayaCard::canView());
        $this->assertTrue(UsulanSayaStats::canView());
        $this->assertTrue(RiwayatUsulanCard::canView());
        $this->assertTrue(UsulanSayaYearChart::canView());
    }

    public function test_dosen_dashboard_widgets_render_without_error(): void
    {
        $this->seed(ProposalSchemeSeeder::class);
        $dosen = $this->actingOtpVerified('dosen');
        Proposal::factory()->forDosen($dosen)->count(3)->create();
        Proposal::factory()->count(4)->create(); // milik orang lain

        foreach ([ProfilSayaCard::class, UsulanSayaStats::class, RiwayatUsulanCard::class, UsulanSayaYearChart::class] as $widget) {
            Livewire::test($widget)->assertOk();
        }

        // Widget dosen tersembunyi dari pengawas.
        $this->actingOtpVerified('admin_lppm');
        $this->assertFalse(ProfilSayaCard::canView());
        $this->assertFalse(UsulanSayaStats::canView());
    }

    public function test_all_dashboard_widgets_render_without_error(): void
    {
        $this->seed(ProposalSchemeSeeder::class);
        $pimpinan = tap(User::factory()->create(['nidn' => '123']), fn (User $u) => $u->assignRole('pimpinan'));
        Proposal::factory()->count(6)->create();

        $this->actingOtpVerified('admin_lppm');

        $widgets = [
            DashboardIdentity::class,
            MonitoringUsulanCard::class,
            MonitoringPelaksanaanCard::class,
            ProfilLembagaCard::class,
            ProfilPimpinanCard::class,
            ProposalsByStatusChart::class,
            ProposalsBySchemeChart::class,
            ProposalsByYearChart::class,
            AnnouncementsWidget::class,
        ];

        foreach ($widgets as $widget) {
            Livewire::test($widget)->assertOk();
        }
    }

    public function test_monitoring_usulan_card_is_scoped_for_dosen(): void
    {
        $dosen = $this->actingOtpVerified('dosen');
        Proposal::factory()->forDosen($dosen)->count(2)->create();
        Proposal::factory()->count(5)->create(); // milik orang lain

        $data = (new MonitoringUsulanCard)->getViewData();
        $draftAtauLebih = collect($data['items'])->firstWhere(0, 'Usulan Draft')[1]
            + collect($data['items'])->firstWhere(0, 'Dikirim Pengusul')[1];

        $this->assertSame(2, $draftAtauLebih, 'dosen hanya melihat 2 usulan miliknya');
    }
}
