<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed role & permission dasar. Aman dipanggil di setiap test karena
     * seeder-nya idempoten (findOrCreate).
     */
    protected function seedRoles(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }
}
