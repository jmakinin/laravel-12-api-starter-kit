<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create permissions
        Permission::firstOrCreate(['name' => 'view_dashboard', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'detete', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view_logs', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'manage_permissions', 'guard_name' => 'api']);

        // Create roles and assign existing permissions
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        $role->givePermissionTo([
            'view_dashboard',
            'create',
            'edit',
            'detete',
            'view',
            'view_logs',
            'manage_permissions'
        ]);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $role->givePermissionTo([
            'view_dashboard',
            'create',
            'edit',
            'detete',
            'view',
            'manage_permissions'
        ]);

        $role = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'api']);
        $role->givePermissionTo([
            'view_dashboard',
            'view',
        ]);

        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $role->givePermissionTo([
            'view_dashboard',
            'create',
            'edit',
            'view',
        ]);
    }
}
