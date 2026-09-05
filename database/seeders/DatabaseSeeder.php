<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $this->createUser('Super Admin', 'superadmin@sip2m.test', RoleEnum::SuperAdmin, phone: '081200000000');
        $this->createUser('Admin LPPM', 'admin@sip2m.test', RoleEnum::AdminLppm, phone: '081200000001');
        $this->createUser('Dr. Iwan Kurniawan, M.Kom.', 'pimpinan@sip2m.test', RoleEnum::Pimpinan, phone: '081200000002', nidn: '0401017001');
        $this->createUser('Prof. Wisnu Nurcahyo', 'reviewer@sip2m.test', RoleEnum::Reviewer, phone: '081200000003', nidn: '0021026504', kompetensi: 'Ilmu Komputer / Rekayasa Perangkat Lunak');
        $this->createUser('Dr. Satria Jaya Priatna', 'reviewer2@sip2m.test', RoleEnum::Reviewer, phone: '081200000004', nidn: '0015016404', kompetensi: 'Teknik Lingkungan / Pengelolaan Sumber Daya');

        // Dosen dengan NIDN. Nomor HP sengaja dikosongkan untuk mendemokan
        // langkah "Lengkapi Profil" pada gerbang OTP.
        $this->createUser('Dosen Satu', 'dosen@sip2m.test', RoleEnum::Dosen, phone: null, nidn: '0401019001');
        $this->createUser('Dosen Dua', 'dosen2@sip2m.test', RoleEnum::Dosen, phone: '081200000006', nidn: '0402029002');

        $this->call(ProposalSchemeSeeder::class);
        $this->call(ProgramStudiSeeder::class);
        $this->call(DemoProposalSeeder::class);
        $this->call(AnnouncementSeeder::class);
    }

    private function createUser(
        string $name,
        string $email,
        RoleEnum $role,
        ?string $phone = null,
        ?string $nidn = null,
        ?string $kompetensi = null,
    ): void {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_number' => $phone,
                'nidn' => $nidn,
                'kompetensi' => $kompetensi,
            ],
        );

        $user->syncRoles([$role->value]);
    }
}
