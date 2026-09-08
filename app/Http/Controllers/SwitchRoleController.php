<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Mengganti "role aktif" akun multi-role (lihat {@see \App\Models\User::activeRole()}).
 * Hanya mengubah tampilan menu/dasbor untuk sesi ini — bukan otorisasi data.
 */
class SwitchRoleController extends Controller
{
    public function __invoke(Request $request, string $role): RedirectResponse
    {
        $target = Role::tryFrom($role);
        $user = $request->user();

        if ($target !== null && $user?->hasRole($target->value)) {
            session(['active_role' => $target->value]);
        }

        return redirect()->to('/admin');
    }
}
