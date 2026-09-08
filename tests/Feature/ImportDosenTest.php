<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\User\ImportDosenFromSinta;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ImportDosenTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): string
    {
        return base_path('tests/Fixtures/sinta_dosen_sample.csv');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_import_creates_users_and_resolves_program_studi(): void
    {
        ProgramStudi::create(['kode' => '55201', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'aktif' => true]);

        $result = app(ImportDosenFromSinta::class)($this->fixture());

        $this->assertSame(5, $result->created);
        $this->assertSame(0, $result->updated);
        $this->assertSame([], $result->skipped);

        $toni = User::where('sinta_id', '5974273')->first();
        $this->assertNotNull($toni);
        $this->assertSame('Toni Wijanarko Adi Putra, S.Kom, M.Kom', $toni->name);
        $this->assertTrue($toni->hasRole('dosen'));
        $this->assertSame('Teknik Informatika', $toni->programStudi->nama);
        $this->assertSame('sinta5974273@dosen.local', $toni->email);
        $this->assertSame('S2', $toni->pendidikan_terakhir);
        $this->assertSame(771.5, $toni->sinta_score_overall_v2);
        $this->assertSame(391.5, $toni->sinta_score_3yr_v2);
        $this->assertSame(1123.87, $toni->sinta_score_overall_v3);
        $this->assertSame(620.2, $toni->sinta_score_3yr_v3);

        // Prodi sudah ada -> dipakai ulang, tidak duplikat.
        $this->assertSame(1, ProgramStudi::where('nama', 'Teknik Informatika')->count());

        // Prodi baru -> otomatis dibuat.
        $baru = ProgramStudi::where('nama', 'Desain Komunikasi Visual')->first();
        $this->assertNotNull($baru);
        $this->assertSame('S1', $baru->jenjang->value);

        $iwan = User::where('sinta_id', '6006274')->first();
        $this->assertStringContainsString('Dr Iwan Koerniawan', $iwan->name);
        $this->assertStringContainsString('S.E., M.Si, M.Th', $iwan->name);

        // NIDN yang tidak valid (berisi teks nama) tidak disimpan.
        $hastu = User::where('sinta_id', '6800671')->first();
        $this->assertNull($hastu->nidn);

        // Prodi kosong -> program_studi_id null, tidak error.
        $rini = User::where('sinta_id', '6038460')->first();
        $this->assertNull($rini->program_studi_id);
    }

    public function test_reimporting_the_same_file_updates_instead_of_duplicating(): void
    {
        app(ImportDosenFromSinta::class)($this->fixture());
        $result = app(ImportDosenFromSinta::class)($this->fixture());

        $this->assertSame(0, $result->created);
        $this->assertSame(5, $result->updated);
        $this->assertSame(5, User::count());
    }

    public function test_admin_lppm_can_import_via_the_admin_panel(): void
    {
        $admin = User::factory()->create(['phone_number' => '081234567890']);
        $admin->assignRole('admin_lppm');
        $this->actingAs($admin)->withSession([config('sip2m.otp.session_key') => $admin->getKey()]);

        Livewire::test(ListUsers::class)
            ->callAction('importDosen', data: [
                'file' => UploadedFile::fake()->createWithContent(
                    'export.csv',
                    file_get_contents($this->fixture()),
                ),
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(5, User::where('sinta_id', '!=', null)->count());
    }

    public function test_dosen_cannot_see_the_import_action(): void
    {
        $dosen = User::factory()->create(['phone_number' => '081234567890']);
        $dosen->assignRole('dosen');
        $this->actingAs($dosen)->withSession([config('sip2m.otp.session_key') => $dosen->getKey()]);

        $this->get(\App\Filament\Resources\UserResource::getUrl('index'))->assertForbidden();
    }
}
