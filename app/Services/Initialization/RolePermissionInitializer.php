<?php

namespace App\Services\Initialization;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionInitializer implements InitializerInterface
{
    public function getName(): string
    {
        return 'Roles and Permissions';
    }

    public function initialize(bool $dryRun = false): InitializationResult
    {
        $result = new InitializationResult();

        // 1. Initialize Permissions
        $permissions = config('acl.permissions', []);
        
        foreach ($permissions as $prefix => $permissionGroup) {
            foreach ($permissionGroup as $value) {
                $permissionName = $prefix . '.' . $value;

                $exists = Permission::query()->where('name', $permissionName)->exists();
                
                if ($exists) {
                    $result->record('skip', sprintf('Permission already exists: %s', $permissionName));
                } else {
                    if (!$dryRun) {
                        Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                    }
                    $result->record('create', sprintf('Permission: %s', $permissionName));
                }
            }
        }

        // 2. Initialize Roles and Mappings
        $rolesToInitialize = ['admin', 'instructor'];

        foreach ($rolesToInitialize as $roleName) {
            $roleExists = Role::query()->where('name', $roleName)->exists();

            if ($roleExists) {
                $result->record('skip', sprintf('Role already exists: %s', $roleName));
                continue;
            }

            if (!$dryRun) {
                $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
                
                // Map permissions only for newly created roles
                if ($roleName === 'admin') {
                    $role->syncPermissions(Permission::all());
                    $result->record('create', 'Mapped all permissions to newly created admin role');
                } elseif ($roleName === 'instructor') {
                    $instructorPrefixes = ['course', 'contact', 'category', 'chapter', 'exam', 'quiz', 'admin', 'report'];
                    $instructorPermissions = Permission::query()
                        ->where(function ($query) use ($instructorPrefixes) {
                            foreach ($instructorPrefixes as $prefix) {
                                $query->orWhere('name', 'like', $prefix . '.%');
                            }
                        })
                        ->get();
                    $role->syncPermissions($instructorPermissions);
                    $result->record('create', 'Mapped default permissions to newly created instructor role');
                }
            }

            $result->record('create', sprintf('Role: %s', $roleName));
        }

        return $result;
    }
}
