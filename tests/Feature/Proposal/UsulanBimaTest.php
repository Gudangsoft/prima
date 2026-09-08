<?php

declare(strict_types=1);

namespace Tests\Feature\Proposal;

use App\Enums\MemberApprovalStatus;
use App\Enums\MemberType;
use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource\Pages\CreateProposal;
use App\Filament\Resources\ProposalResource\Pages\ViewProposal;
use App\Filament\Widgets\UndanganTimCard;
use App\Models\Proposal;
use App\Models\ProposalMember;
use App\Models\ProposalRabItem;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsulanBimaTest extends TestCase
{
    use RefreshDatabase;

    private User $ketua;

    private User $rekan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $this->ketua = tap(User::factory()->create(['phone_number' => '0812']), fn (User $u) => $u->assignRole('dosen'));
        $this->rekan = tap(User::factory()->create(['name' => 'REKAN DOSEN', 'nidn' => '0099887766']), fn (User $u) => $u->assignRole('dosen'));
    }

    private function actAs(User $u): self
    {
        $this->actingAs($u)->withSession([config('sip2m.otp.session_key') => $u->getKey()]);

        return $this;
    }

    private function draftWithFile(): Proposal
    {
        return Proposal::factory()
            ->forDosen($this->ketua)
            ->withFile()
            ->create(['status' => ProposalStatus::Draft->value]);
    }

    public function test_create_page_shows_all_bima_wizard_steps(): void
    {
        $this->actAs($this->ketua);

        $this->get('/admin/usulan/create')
            ->assertOk()
            ->assertSee('Identitas Usulan')
            ->assertSee('Anggota Tim')
            ->assertSee('Target Luaran')
            ->assertSee('8 Bidang Strategis')
            ->assertSee('RAB')
            ->assertSee('Mitra');
    }

    public function test_create_page_renders_with_scheme_that_has_a_template(): void
    {
        ProposalScheme::factory()->penelitian()->create(['template_path' => 'skema-template/contoh.pdf']);
        $this->actAs($this->ketua);

        $this->get('/admin/usulan/create')->assertOk();
    }

    public function test_wizard_create_saves_identitas_and_nested_sections(): void
    {
        $scheme = ProposalScheme::factory()->penelitian()->create();
        $this->actAs($this->ketua);

        Livewire::withQueryParams(['kat' => 'penelitian'])
            ->test(CreateProposal::class)
            ->fillForm([
                'scheme_id' => $scheme->id,
                'kelompok_skema' => 'Riset Dasar',
                'bidang_fokus' => 'tik',
                'tema' => 'Ekonomi digital',
                'lama_kegiatan' => 2,
                'tahun_usulan' => 2026,
                'tahun_anggaran' => 2027,
                'target_tkt' => 3,
                'judul' => 'Platform E-Katalog UMKM',
                'abstrak' => str_repeat('Ringkasan substansi usulan penelitian ini. ', 6),
                'members' => [
                    ['jenis' => 'dosen', 'user_id' => $this->rekan->id, 'nama' => $this->rekan->name, 'tugas' => 'Analisis data'],
                    ['jenis' => 'mahasiswa', 'user_id' => null, 'nama' => 'MHS SATU', 'identitas_no' => '1124', 'jenjang' => 'S1', 'tugas' => 'Survei'],
                ],
                'outputTargets' => [
                    ['tahun_ke' => 1, 'kelompok_luaran' => 'Artikel di Jurnal', 'jenis_luaran' => 'Jurnal Internasional', 'target' => 'Published'],
                ],
                'rabItems' => [
                    ['tahun_ke' => 1, 'kelompok' => 'Bahan', 'komponen' => 'ATK', 'item' => 'Kertas', 'satuan' => 'Paket', 'harga_satuan' => 500000, 'volume' => 3],
                ],
                'strategicFields' => [
                    ['bidang' => 'Ekonomi', 'rumusan_masalah' => 'Daya saing rendah', 'uraian_kegiatan' => 'Pendampingan'],
                ],
                'partners' => [
                    ['nama_mitra' => 'Koperasi Maju', 'institusi' => 'Koperasi', 'negara' => 'Indonesia', 'dana' => 0],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $proposal = Proposal::query()->where('judul', 'Platform E-Katalog UMKM')->firstOrFail();

        $this->assertSame($this->ketua->id, $proposal->user_id);
        $this->assertSame('Riset Dasar', $proposal->kelompok_skema);
        $this->assertSame('tik', $proposal->bidang_fokus->value);
        $this->assertSame(2, $proposal->lama_kegiatan);
        $this->assertCount(2, $proposal->members);
        $this->assertCount(1, $proposal->outputTargets);
        $this->assertCount(1, $proposal->rabItems);
        $this->assertCount(1, $proposal->strategicFields);
        $this->assertCount(1, $proposal->partners);
        $this->assertSame(1_500_000.0, $proposal->total_rab);

        // Anggota dosen baru berstatus menunggu.
        $this->assertSame(
            MemberApprovalStatus::Menunggu,
            $proposal->members->firstWhere('jenis', MemberType::Dosen)->status,
        );
    }

    public function test_submit_is_blocked_until_all_dosen_members_approve(): void
    {
        $proposal = $this->draftWithFile();
        $member = ProposalMember::create([
            'proposal_id' => $proposal->id,
            'jenis' => MemberType::Dosen->value,
            'user_id' => $this->rekan->id,
            'nama' => $this->rekan->name,
            'status' => MemberApprovalStatus::Menunggu->value,
        ]);

        $this->assertFalse($this->ketua->can('submit', $proposal->fresh()));

        $member->update(['status' => MemberApprovalStatus::Menyetujui->value]);

        $this->assertTrue($this->ketua->can('submit', $proposal->fresh()));
    }

    public function test_invited_member_views_proposal_and_approves_from_detail(): void
    {
        $proposal = $this->draftWithFile();
        ProposalMember::create([
            'proposal_id' => $proposal->id,
            'jenis' => MemberType::Dosen->value,
            'user_id' => $this->rekan->id,
            'nama' => $this->rekan->name,
            'status' => MemberApprovalStatus::Menunggu->value,
        ]);

        // Anggota boleh membuka detail meski bukan pemilik.
        $this->actAs($this->rekan)->get("/admin/usulan/{$proposal->id}")->assertOk();

        Livewire::test(ViewProposal::class, ['record' => $proposal->id])
            ->assertActionVisible('setujuiKeikutsertaan')
            ->callAction('setujuiKeikutsertaan');

        $this->assertSame(
            MemberApprovalStatus::Menyetujui,
            $proposal->members()->where('user_id', $this->rekan->id)->first()->status,
        );
    }

    public function test_undangan_tim_widget_lists_pending_and_responds(): void
    {
        $proposal = $this->draftWithFile();
        ProposalMember::create([
            'proposal_id' => $proposal->id,
            'jenis' => MemberType::Dosen->value,
            'user_id' => $this->rekan->id,
            'nama' => $this->rekan->name,
            'status' => MemberApprovalStatus::Menunggu->value,
        ]);

        $this->actAs($this->rekan);
        $this->assertTrue(UndanganTimCard::canView());

        $memberId = $proposal->members()->where('user_id', $this->rekan->id)->value('id');

        Livewire::test(UndanganTimCard::class)
            ->assertSee($proposal->judul)
            ->call('respond', $memberId, 'setuju');

        $this->assertSame(
            MemberApprovalStatus::Menyetujui,
            $proposal->members()->where('user_id', $this->rekan->id)->first()->status,
        );

        $this->actAs($this->rekan);
        $this->assertFalse(UndanganTimCard::canView(), 'tidak ada undangan tertunda lagi');
    }

    public function test_infolist_renders_bima_sections(): void
    {
        $proposal = $this->draftWithFile();
        ProposalRabItem::create([
            'proposal_id' => $proposal->id, 'tahun_ke' => 1, 'kelompok' => 'Bahan',
            'komponen' => 'ATK', 'item' => 'Kertas', 'satuan' => 'Paket', 'harga_satuan' => 500000, 'volume' => 2,
        ]);

        $this->actAs($this->ketua)
            ->get("/admin/usulan/{$proposal->id}")
            ->assertOk()
            ->assertSee('Identitas Usulan')
            ->assertSee('Identitas Anggota Dosen')
            ->assertSee('Rancangan Anggaran Biaya (RAB)')
            ->assertSee('Ketua Pengusul');
    }
}
