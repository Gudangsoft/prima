<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Permissions.
        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // 2. Roles + pemetaan permission.
        $map = Permissions::forRoles();

        foreach (RoleEnum::cases() as $role) {
            $model = Role::findOrCreate($role->value, 'web');
            $model->syncPermissions($map[$role->value] ?? []);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
