<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProgramStudi;
use App\Models\User;

class ProgramStudiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('prodi.viewAny');
    }

    public function view(User $user, ProgramStudi $prodi): bool
    {
        return $user->can('prodi.view');
    }

    public function create(User $user): bool
    {
        return $user->can('prodi.create');
    }

    public function update(User $user, ProgramStudi $prodi): bool
    {
        return $user->can('prodi.update');
    }

    public function delete(User $user, ProgramStudi $prodi): bool
    {
        return $user->can('prodi.delete') && $prodi->dosen()->doesntExist();
    }
}
