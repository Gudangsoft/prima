<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Imports\DosenImporter;
use App\Filament\Imports\ProgramStudiImporter;
use App\Filament\Pages\SinkronisasiDosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataPendukungTest extends TestCase
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

    private function runImport(string $importerClass, array $columns, array $rows): Import
    {
        $admin = User::query()->role('admin_lppm')->first() ?? $this->actingOtpVerified('admin_lppm');

        $import = Import::create([
            'user_id' => $admin->id,
            'file_name' => 'test.csv',
            'file_path' => 'test.csv',
            'importer' => $importerClass,
            'total_rows' => count($rows),
        ]);

        $columnMap = array_combine($columns, $columns);
        $importer = new $importerClass($import, $columnMap, []);

        foreach ($rows as $row) {
            $importer($row);
        }

        return $import;
    }

    /* --------------------------------------------------------------------- */

    public function test_program_studi_pages_are_gated_to_admin_lppm(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $this->get('/admin/program-studi')->assertOk();

        $this->actingOtpVerified('dosen');
        $this->get('/admin/program-studi')->assertForbidden();
    }

    public function test_program_studi_table_shows_status_badge(): void
    {
        $this->actingOtpVerified('admin_lppm');
        ProgramStudi::factory()->create(['kode' => '11111', 'nama' => 'Aktif Prodi', 'aktif' => true]);
        ProgramStudi::factory()->create(['kode' => '22222', 'nama' => 'Nonaktif Prodi', 'aktif' => false]);

        $this->get('/admin/program-studi')
            ->assertOk()
            ->assertSeeText('Aktif')
            ->assertSeeText('Non Aktif');
    }

    public function test_dosen_count_links_to_filtered_sinkronisasi_dosen(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $ti = ProgramStudi::factory()->create(['nama' => 'Teknik Informatika']);
        $si = ProgramStudi::factory()->create(['nama' => 'Sistem Informasi']);
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'program_studi_id' => $ti->id]);
        $budi->assignRole('dosen');
        $ani = User::factory()->create(['name' => 'Ani Wijaya', 'program_studi_id' => $si->id]);
        $ani->assignRole('dosen');

        Livewire::test(SinkronisasiDosen::class)
            ->set('prodi', $ti->id)
            ->assertSee('Teknik Informatika')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ani Wijaya');

        $this->assertStringContainsString(
            'prodi='.$ti->id,
            \App\Filament\Pages\SinkronisasiDosen::urlUntukProdi($ti->id),
        );
    }

    public function test_sinkronisasi_dosen_page_is_gated(): void
    {
        $this->actingOtpVerified('admin_lppm');
        $this->get('/admin/sinkronisasi-dosen')->assertOk();

        $this->actingOtpVerified('reviewer');
        $this->get('/admin/sinkronisasi-dosen')->assertForbidden();
    }

    public function test_program_studi_import_creates_and_updates_by_kode(): void
    {
        ProgramStudi::factory()->create(['kode' => '55201', 'nama' => 'Nama Lama']);

        $this->runImport(ProgramStudiImporter::class,
            ['kode', 'nama', 'jenjang', 'fakultas', 'aktif'],
            [
                ['kode' => '55201', 'nama' => 'Teknik Informatika', 'jenjang' => 's1', 'fakultas' => 'FT', 'aktif' => '1'],
                ['kode' => '99999', 'nama' => 'Prodi Baru', 'jenjang' => 'S2', 'fakultas' => 'Pascasarjana', 'aktif' => '1'],
            ],
        );

        $this->assertSame(2, ProgramStudi::count(), 'tidak menduplikasi kode yang sudah ada');
        $this->assertSame('Teknik Informatika', ProgramStudi::where('kode', '55201')->value('nama'));
        $this->assertSame('S1', ProgramStudi::where('kode', '55201')->value('jenjang')->value);
        $this->assertDatabaseHas('program_studi', ['kode' => '99999', 'nama' => 'Prodi Baru']);
    }

    public function test_dosen_import_creates_account_with_role_and_links_prodi(): void
    {
        $prodi = ProgramStudi::factory()->create(['kode' => '55201']);

        $this->runImport(DosenImporter::class,
            [
                'nidn', 'name', 'gelar_depan', 'gelar_belakang', 'sinta_id', 'pendidikan_terakhir',
                'sinta_score_overall_v2', 'sinta_score_3yr_v2', 'sinta_score_overall_v3', 'sinta_score_3yr_v3',
                'phone_number', 'jabatan', 'kompetensi', 'kode_prodi',
            ],
            [[
                'nidn' => '0401019001', 'name' => 'BUDI SANTOSO', 'gelar_depan' => 'Dr', 'gelar_belakang' => 'S.Kom, M.Kom',
                'sinta_id' => '257669', 'pendidikan_terakhir' => 'S2',
                'sinta_score_overall_v2' => '771.5', 'sinta_score_3yr_v2' => '391.5',
                'sinta_score_overall_v3' => '1123.87', 'sinta_score_3yr_v3' => '620.2',
                'phone_number' => '0812', 'jabatan' => 'Lektor', 'kompetensi' => 'RPL', 'kode_prodi' => '55201',
            ]],
        );

        $user = User::where('nidn', '0401019001')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('dosen'));
        $this->assertSame('nidn0401019001@dosen.local', $user->email);
        $this->assertSame('Dr BUDI SANTOSO, S.Kom, M.Kom', $user->name);
        $this->assertSame('257669', $user->sinta_id);
        $this->assertSame('S2', $user->pendidikan_terakhir);
        $this->assertSame(771.5, $user->sinta_score_overall_v2);
        $this->assertSame(391.5, $user->sinta_score_3yr_v2);
        $this->assertSame(1123.87, $user->sinta_score_overall_v3);
        $this->assertSame(620.2, $user->sinta_score_3yr_v3);
        $this->assertSame($prodi->id, $user->program_studi_id);
        $this->assertSame('Lektor', $user->jabatan);
    }

    public function test_dosen_import_updates_existing_account_matched_by_nidn(): void
    {
        $existing = User::factory()->create(['nidn' => '0401019001', 'name' => 'Nama Lama', 'jabatan' => null]);
        $existing->assignRole('dosen');

        $this->runImport(DosenImporter::class,
            ['nidn', 'name', 'jabatan'],
            [['nidn' => '0401019001', 'name' => 'Nama Baru', 'jabatan' => 'Lektor Kepala']],
        );

        $existing->refresh();
        $this->assertSame('Nama Baru', $existing->name);
        $this->assertSame('Lektor Kepala', $existing->jabatan);
        $this->assertSame(1, User::where('nidn', '0401019001')->count());
    }

    public function test_dosen_import_generates_placeholder_email(): void
    {
        $this->runImport(DosenImporter::class,
            ['nidn', 'name', 'jabatan'],
            [['nidn' => '0401019002', 'name' => 'Dr. Ani', 'jabatan' => 'Lektor']],
        );

        $user = User::where('nidn', '0401019002')->first();
        $this->assertNotNull($user);
        $this->assertSame('nidn0401019002@dosen.local', $user->email);
        $this->assertTrue($user->hasRole('dosen'));
    }

    public function test_sinkronisasi_dosen_lists_and_searches_dosen(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $prodi = ProgramStudi::factory()->create(['nama' => 'Teknik Informatika']);
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'nidn' => '0401019001', 'program_studi_id' => $prodi->id]);
        $budi->assignRole('dosen');
        $ani = User::factory()->create(['name' => 'Ani Wijaya', 'nidn' => '0401019002']);
        $ani->assignRole('dosen');

        Livewire::test(SinkronisasiDosen::class)
            ->assertSee('Budi Santoso')
            ->assertSee('Ani Wijaya')
            ->set('cari', 'Budi')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ani Wijaya');
    }

    public function test_dosen_import_matches_by_nuptk_when_nidn_blank(): void
    {
        $this->runImport(DosenImporter::class,
            ['nuptk', 'name', 'jabatan'],
            [['nuptk' => '1234567890123456', 'name' => 'Dosen Tidak Tetap', 'jabatan' => 'Tenaga Pengajar']],
        );

        $user = User::where('nuptk', '1234567890123456')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->nidn);
        $this->assertSame('nuptk1234567890123456@dosen.local', $user->email);
        $this->assertTrue($user->hasRole('dosen'));

        // Impor ulang dengan NUPTK yang sama -> perbarui, tidak duplikat.
        $this->runImport(DosenImporter::class,
            ['nuptk', 'name', 'jabatan'],
            [['nuptk' => '1234567890123456', 'name' => 'Dosen Tidak Tetap Update', 'jabatan' => 'Asisten Ahli']],
        );
        $this->assertSame(1, User::where('nuptk', '1234567890123456')->count());
        $this->assertSame('Asisten Ahli', $user->refresh()->jabatan);
    }

    public function test_dosen_import_rejects_row_without_nidn_or_nuptk(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->runImport(DosenImporter::class,
            ['name'],
            [['name' => 'Tanpa Identitas']],
        );
    }

    public function test_detail_dosen_action_shows_pddikti_and_sinta_summary(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $prodi = ProgramStudi::factory()->create(['nama' => 'Sistem Komputer']);
        $dosen = User::factory()->create([
            'name' => 'Danang, S.Kom, M.T',
            'nidn' => '0615098702',
            'nuptk' => '804776566131103',
            'program_studi_id' => $prodi->id,
            'pendidikan_terakhir' => 'S2',
            'jabatan' => 'Lektor',
            'sinta_id' => '5976759',
            'sinta_score_overall_v3' => 858.0,
        ]);
        $dosen->assignRole('dosen');

        Livewire::test(SinkronisasiDosen::class)
            ->mountAction('detailDosen', arguments: ['dosen' => $dosen->id])
            ->assertOk()
            ->assertSee('Data PDDIKTI')
            ->assertSee('Data SINTA')
            ->assertSee('Sistem Komputer')
            ->assertSee('5976759')
            ->assertFormSet(['email' => $dosen->email], 'mountedActionForm');
    }

    public function test_detail_dosen_action_updates_contact_info(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $dosen = User::factory()->create(['email' => 'lama@dosen.local']);
        $dosen->assignRole('dosen');

        Livewire::test(SinkronisasiDosen::class)
            ->mountAction('detailDosen', arguments: ['dosen' => $dosen->id])
            ->setActionData(['email' => 'baru@kampus.ac.id', 'phone_number' => '0812', 'telepon' => '0271123456'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $dosen->refresh();
        $this->assertSame('baru@kampus.ac.id', $dosen->email);
        $this->assertSame('0271123456', $dosen->telepon);
    }

    public function test_detail_dosen_action_blocks_duplicate_email(): void
    {
        $this->actingOtpVerified('admin_lppm');

        $lain = User::factory()->create(['email' => 'dipakai@kampus.ac.id']);
        $dosen = User::factory()->create(['email' => 'lama@dosen.local']);
        $dosen->assignRole('dosen');

        Livewire::test(SinkronisasiDosen::class)
            ->mountAction('detailDosen', arguments: ['dosen' => $dosen->id])
            ->setActionData(['email' => $lain->email, 'phone_number' => null, 'telepon' => null])
            ->callMountedAction();

        $this->assertSame('lama@dosen.local', $dosen->refresh()->email);
    }
}
