<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'admin']);
        Role::updateOrCreate(['name' => 'instructor']);

        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $role = Role::where('name', 'admin')->first();
            $role->givePermissionTo($permission);
        }

        $permissions = Permission::all()->skip(18)->take(47);

        foreach ($permissions as $permission) {
            $role = Role::where('name', 'instructor')->first();
            $role->givePermissionTo($permission);
        }
    }
}
