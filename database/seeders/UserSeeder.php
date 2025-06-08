<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // remove old admin account if exist
        User::where('email', 'super_admin@tenet.com')->forceDelete();

        $user1 = User::firstOrCreate([
            'email' => 'super_admin@tenet.com',
        ], [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'phone' => '02000001122',
            'password' => 'password',
            'status' => 'active',
        ]);

        // Get role
        $superAdminRole = Role::where('name', 'super_admin')->first();

        // Assign the role to the user
        if ($superAdminRole) {
            $user1->assignRole($superAdminRole);
        }

        // remove old admin account if exist
        User::where('email', 'admin@tenet.com')->forceDelete();

        $user2 = User::firstOrCreate([
            'email' => 'admin@tenet.com',
        ], [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'phone' => '02000001122',
            'password' => 'password',
            'status' => 'active',
        ]);

        // Get role
        $adminRole = Role::where('name', 'admin')->first();

        // Assign the role to the user
        if ($adminRole) {
            $user2->assignRole($adminRole);
        }


        // remove old manager account if exist
        User::where('email', 'manager@tenet.com')->forceDelete();

        $user3 = User::firstOrCreate([
            'email' => 'manager@tenet.com',
        ], [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'phone' => '0200011122',
            'password' => 'password',
            'status' => 'active',
        ]);

        // Get role
        $managerRole = Role::where('name', 'manager')->first();

        // Assign the role to the user
        if ($managerRole) {
            $user3->assignRole($managerRole);
        }


        // remove old viewer account if exist
        User::where('email', 'viewer@tenet.com')->forceDelete();

        $user4 = User::firstOrCreate([
            'email' => 'viewer@tenet.com',
        ], [
            'firstname' => 'Jeane',
            'lastname' => 'Doe',
            'phone' => '0200011122',
            'password' => 'password',
            'status' => 'active',
        ]);

        // Get role
        $viewerRole = Role::where('name', 'viewer')->first();

        // Assign the role to the user
        if ($viewerRole) {
            $user4->assignRole($viewerRole);
        }
    }
}
