<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'scheme_id' => ProposalScheme::factory(),
            'judul' => ucfirst($this->faker->sentence(8)),
            'abstrak' => $this->faker->paragraphs(3, true),
            'file_proposal' => null,
            'status' => ProposalStatus::Draft->value,
            'tahun_anggaran' => (int) now()->year,
        ];
    }

    public function withFile(): static
    {
        return $this->state(fn () => ['file_proposal' => 'proposals/dummy-'.$this->faker->uuid().'.pdf']);
    }

    public function status(ProposalStatus $status): static
    {
        return $this->state(fn () => [
            'status' => $status->value,
            'file_proposal' => 'proposals/dummy-'.$this->faker->uuid().'.pdf',
        ]);
    }

    public function forDosen(User $dosen): static
    {
        return $this->state(fn () => ['user_id' => $dosen->getKey()]);
    }

    public function forScheme(ProposalScheme $scheme): static
    {
        return $this->state(fn () => ['scheme_id' => $scheme->getKey()]);
    }
}
