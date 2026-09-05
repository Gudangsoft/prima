<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Kategori;
use App\Models\ProposalScheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalScheme>
 */
class ProposalSchemeFactory extends Factory
{
    protected $model = ProposalScheme::class;

    public function definition(): array
    {
        return [
            'nama_skema' => ucwords($this->faker->unique()->words(3, true)),
            'kategori' => $this->faker->randomElement(Kategori::cases())->value,
            'deskripsi' => $this->faker->paragraph(),
            'aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['aktif' => false]);
    }

    public function penelitian(): static
    {
        return $this->state(fn () => ['kategori' => Kategori::Penelitian->value]);
    }

    public function pengabdian(): static
    {
        return $this->state(fn () => ['kategori' => Kategori::Pengabdian->value]);
    }
}
