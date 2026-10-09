<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $adminRole->givePermissionTo(Permission::findOrCreate('manage catalog', 'web'));
        $adminRole->givePermissionTo(Permission::findOrCreate('manage orders', 'web'));
        $adminRole->givePermissionTo(Permission::findOrCreate('manage delivery zones', 'web'));

        Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);
    }
}
