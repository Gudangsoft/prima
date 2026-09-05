<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (Announcement::query()->exists()) {
            return;
        }

        $admin = User::query()->role(Role::AdminLppm->value)->first();

        foreach (config('sip2m.announcements', []) as $i => $a) {
            Announcement::create([
                'judul' => $a['judul'],
                'isi' => '<p>'.e($a['isi']).'</p>',
                'tanggal_terbit' => $this->parseDate($a['tanggal'] ?? null),
                'disematkan' => $i === 0,
                'terbit' => true,
                'created_by' => $admin?->id,
            ]);
        }
    }

    private function parseDate(?string $value): Carbon
    {
        $bulan = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
            'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
        ];

        if ($value && preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $value, $m) && isset($bulan[$m[2]])) {
            return Carbon::create((int) $m[3], $bulan[$m[2]], (int) $m[1]);
        }

        return now();
    }
}
