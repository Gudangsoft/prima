<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Otorisasi manajemen pengguna. Praktis hanya Admin LPPM yang punya
 * permission tulis; Pimpinan bisa melihat; Super Admin di-bypass Gate::before.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.viewAny');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.update');
    }

    public function delete(User $user, User $model): bool
    {
        // Tidak boleh menghapus akun sendiri.
        return $user->can('users.delete') && $user->isNot($model);
    }
}
