<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $createdPermissions = [];

        $permissions = [
            'View Users',
            'Add User',
            'Edit User',
            'Delete User',
            'Can Upload PDF',
        ];

        foreach ($permissions as $permission) {
            if (! Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
                $createdPermissions[] = $permission;
            }
        }

        if (count($createdPermissions)) {
            $adminRole->syncPermissions($createdPermissions);
        }
    }
}
