<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Pengaturan hak akses (peran & izin) hanya untuk Super Admin.
 * Super Admin sendiri sudah di-bypass Gate::before(); pemeriksaan di sini
 * berlaku untuk peran lain (selalu ditolak).
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::SuperAdmin->value);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasRole(RoleEnum::SuperAdmin->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::SuperAdmin->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasRole(RoleEnum::SuperAdmin->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole(RoleEnum::SuperAdmin->value)
            && ! in_array($role->name, RoleEnum::values(), true)   // peran bawaan tidak boleh dihapus
            && $role->users()->doesntExist();                       // tidak sedang dipakai
    }
}
