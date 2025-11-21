<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        if (! User::where('email', 'admin@yopmail.com')->exists()) {
            $adminUser = new User([
                'name' => 'Admin Admin',
                'email' => 'admin@yopmail.com',
                'username' => 'admin',
                'password' => bcrypt('password'),
            ]);

            $adminUser->save();
            $adminUser->assignRole($adminRole);
        }
    }
}
