<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = config('acl.permissions');

        foreach ($permissions as $prefix => $permission) {
            foreach ($permission as $value) {
                Permission::firstOrcreate(['name' => $prefix . '.' . $value]);
            }
        }
    }
}
