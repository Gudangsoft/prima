<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\ProfilLembaga;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilLembagaTabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $u = User::factory()->create(['phone_number' => '0812']);
        $u->assignRole('super_admin');
        $this->actingAs($u)->withSession([config('sip2m.otp.session_key') => $u->getKey()]);
    }

    public function test_page_switches_between_penelitian_and_pengabdian_tabs(): void
    {
        Settings::setMany([
            'pen_pimpinan_nama' => 'Prof. A', 'pen_pimpinan_nidn' => '111',
            'pkm_pimpinan_nama' => 'Prof. B', 'pkm_pimpinan_nidn' => '222',
            'pen_nama_lembaga' => 'Lembaga Penelitian X',
            'pkm_nama_lembaga' => 'Lembaga Pengabdian Y',
        ]);

        Livewire::test(ProfilLembaga::class)
            ->assertSet('tab', 'penelitian')
            ->assertSee('Lembaga Penelitian X')
            ->assertSee('PROF. A')
            ->assertSee('111')
            ->call('setTab', 'pengabdian')
            ->assertSet('tab', 'pengabdian')
            ->assertSee('Lembaga Pengabdian Y')
            ->assertSee('PROF. B')
            ->assertSee('222')
            ->assertDontSee('PROF. A');
    }

    public function test_pengabdian_falls_back_to_penelitian_when_empty(): void
    {
        // Kosongkan default pengabdian dari config agar fallback teruji.
        config()->set('sip2m.institution.pengabdian.nama_lembaga', '');
        Settings::setMany(['pen_nama_lembaga' => 'Satu-Satunya Lembaga']);

        $this->assertSame('Satu-Satunya Lembaga', Settings::institution('pengabdian')['nama_lembaga']);
    }

    public function test_pengabdian_uses_its_own_value_when_set(): void
    {
        Settings::setMany([
            'pen_nama_lembaga' => 'Lembaga Penelitian',
            'pkm_nama_lembaga' => 'Lembaga PKM Sendiri',
        ]);

        $this->assertSame('Lembaga PKM Sendiri', Settings::institution('pengabdian')['nama_lembaga']);
        $this->assertSame('Lembaga Penelitian', Settings::institution('penelitian')['nama_lembaga']);
    }
}
