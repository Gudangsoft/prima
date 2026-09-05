<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProposalScheme;
use App\Models\User;

/**
 * Skema usulan dikelola penuh oleh Admin LPPM. Pimpinan hanya membaca.
 * Super Admin di-bypass via Gate::before().
 */
class ProposalSchemePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('schemes.viewAny');
    }

    public function view(User $user, ProposalScheme $scheme): bool
    {
        return $user->can('schemes.view');
    }

    public function create(User $user): bool
    {
        return $user->can('schemes.create');
    }

    public function update(User $user, ProposalScheme $scheme): bool
    {
        return $user->can('schemes.update');
    }

    public function delete(User $user, ProposalScheme $scheme): bool
    {
        // Skema yang sudah dipakai usulan tidak boleh dihapus.
        return $user->can('schemes.delete') && $scheme->proposals()->doesntExist();
    }
}
