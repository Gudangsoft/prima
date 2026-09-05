<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Filament\Pages\DaftarUsulan;
use App\Filament\Widgets\MonitoringUsulanStats;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonitoringUsulanStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function actingOtpVerified(string $role): User
    {
        $user = User::factory()->create(['phone_number' => '0812']);
        $user->assignRole($role);
        $this->actingAs($user)->withSession([config('sip2m.otp.session_key') => $user->getKey()]);

        return $user;
    }

    private function cardValue(array $data, string $label): int
    {
        return collect($data['cards'])->firstWhere('label', $label)['value'];
    }

    public function test_cards_are_role_scoped(): void
    {
        $dosen = $this->actingOtpVerified('dosen');
        Proposal::factory()->forDosen($dosen)->count(2)->create();               // 2 draf milik dosen
        Proposal::factory()->count(4)->create();                                 // 4 draf orang lain
        Proposal::factory()->status(ProposalStatus::Submitted)->count(3)->create();

        // Dosen: hanya miliknya.
        $data = Livewire::test(MonitoringUsulanStats::class)->instance()->getViewData();
        $this->assertSame(2, $this->cardValue($data, 'Usulan Draft'));
        $this->assertSame(0, $this->cardValue($data, 'Usulan Dikirim'));

        // Admin LPPM: seluruh institusi.
        $this->actingOtpVerified('admin_lppm');
        $data = Livewire::test(MonitoringUsulanStats::class)->instance()->getViewData();
        $this->assertSame(6, $this->cardValue($data, 'Usulan Draft'));
        $this->assertSame(3, $this->cardValue($data, 'Usulan Dikirim'));
        $this->assertSame(3, $this->cardValue($data, 'Usulan Belum Ditinjau'));
    }

    public function test_widget_has_all_seven_bima_cards(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $labels = collect(Livewire::test(MonitoringUsulanStats::class)->instance()->getViewData()['cards'])
            ->pluck('label')->all();

        $this->assertEqualsCanonicalizing([
            'Usulan Draft', 'Usulan Dikirim', 'Usulan Belum Ditinjau', 'Usulan Disetujui',
            'Usulan Ditolak', 'Usulan Didanai', 'Hasil Review Masuk',
        ], $labels);
    }

    public function test_every_card_links_to_its_drilldown_page(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $cards = Livewire::test(MonitoringUsulanStats::class)->instance()->getViewData()['cards'];

        $pageUrl = DaftarUsulan::getUrl();

        foreach ($cards as $card) {
            $this->assertArrayHasKey('url', $card, "Kartu \"{$card['label']}\" tidak punya URL.");
            $this->assertStringStartsWith($pageUrl, $card['url']);
            $this->assertStringContainsString('grup=', $card['url']);
        }
    }

    public function test_list_page_renders_with_header_widget_and_export(): void
    {
        $this->actingOtpVerified('admin_lppm');
        Proposal::factory()->count(3)->create();

        $this->get('/admin/usulan')->assertOk();
    }

    public function test_drilldown_page_renders_for_each_group_and_scopes_rows(): void
    {
        $dosen = $this->actingOtpVerified('dosen');
        Proposal::factory()->forDosen($dosen)->count(2)->create();
        Proposal::factory()->status(ProposalStatus::Submitted)->count(3)->create();

        foreach (['draft', 'dikirim', 'belum-ditinjau', 'disetujui', 'ditolak', 'didanai', 'review-masuk'] as $grup) {
            Livewire::withQueryParams(['grup' => $grup])
                ->test(DaftarUsulan::class)
                ->assertOk()
                ->assertCountTableRecords($grup === 'draft' ? 2 : 0); // dosen hanya melihat 2 draf miliknya
        }

        $this->actingOtpVerified('admin_lppm');
        Livewire::withQueryParams(['grup' => 'belum-ditinjau'])
            ->test(DaftarUsulan::class)
            ->assertOk()
            ->assertCountTableRecords(3);
    }

    public function test_drilldown_page_rejects_unknown_group(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $this->get(DaftarUsulan::getUrl(['grup' => 'entah-apa']))->assertNotFound();
    }

    public function test_rekap_breaks_down_counts_per_scheme(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $a = ProposalScheme::factory()->create(['nama_skema' => 'Skema A']);
        $b = ProposalScheme::factory()->create(['nama_skema' => 'Skema B']);
        ProposalScheme::factory()->create(['nama_skema' => 'Skema Kosong']); // tanpa usulan

        Proposal::factory()->forScheme($a)->count(2)->create();                                    // 2 draf
        Proposal::factory()->forScheme($a)->status(ProposalStatus::Submitted)->count(3)->create();  // 3 belum ditinjau
        Proposal::factory()->forScheme($a)->status(ProposalStatus::Funded)->create();               // 1 didanai
        Proposal::factory()->forScheme($b)->status(ProposalStatus::Rejected)->count(2)->create();    // 2 ditolak

        $rekap = collect(
            Livewire::test(MonitoringUsulanStats::class)->instance()->getViewData()['rekap']
        );

        // Hanya skema yang punya usulan, urut nama.
        $this->assertSame(['Skema A', 'Skema B'], $rekap->pluck('skema')->all());

        $rowA = $rekap->firstWhere('skema', 'Skema A');
        $this->assertSame(2, $rowA['draft']);
        $this->assertSame(4, $rowA['dikirim']);        // 3 submitted + 1 funded
        $this->assertSame(3, $rowA['belum_ditinjau']);
        $this->assertSame(1, $rowA['disetujui']);      // funded termasuk disetujui
        $this->assertSame(0, $rowA['ditolak']);
        $this->assertSame(1, $rowA['didanai']);
        $this->assertStringContainsString('tableFilters', $rowA['url']);

        $rowB = $rekap->firstWhere('skema', 'Skema B');
        $this->assertSame(2, $rowB['dikirim']);
        $this->assertSame(2, $rowB['ditolak']);
        $this->assertSame(0, $rowB['disetujui']);
    }
}
