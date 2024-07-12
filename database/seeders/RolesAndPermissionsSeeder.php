<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'access admin panel']);
        Permission::create(['name' => 'manage team']);
        Permission::create(['name' => 'perform work']);

        // create roles and assign created permissions
        $role = Role::create(['name' => 'admin'])
              ->givePermissionTo(['access admin panel', 'manage team', 'perform work']);

        $role = Role::create(['name' => 'team_admin'])
              ->givePermissionTo(['manage team', 'perform work']);

        $role = Role::create(['name' => 'worker'])
              ->givePermissionTo('perform work');
    }
}
