<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Jenjang;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramStudi>
 */
class ProgramStudiFactory extends Factory
{
    protected $model = ProgramStudi::class;

    public function definition(): array
    {
        return [
            'kode' => (string) $this->faker->unique()->numberBetween(10000, 99999),
            'nama' => 'Program Studi '.ucwords($this->faker->unique()->words(2, true)),
            'jenjang' => $this->faker->randomElement(Jenjang::cases())->value,
            'fakultas' => 'Fakultas '.ucwords($this->faker->word()),
            'aktif' => true,
        ];
    }
}
