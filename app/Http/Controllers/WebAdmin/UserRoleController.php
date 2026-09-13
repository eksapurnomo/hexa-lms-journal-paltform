<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index()
    {
        $users = UserRepository::query()->whereHas('instructor')->withTrashed()->get();
        $roles = Role::all();
        return view('role.index', compact('users', 'roles'));
    }
    public function create()
    {
        $roles = Role::all();
        return view('role.create', compact('roles'));
    }
    public function store(Request $request)
    {
        $request->validate([
            "role_title" => "required|string",
        ]);

        $filteredRoleName = strtolower($request->role_title);

        $role = Role::where('name', $filteredRoleName)->first();

        if ($role) {
            return back()->with('title_exists', 'The name you entered already exists. Please try a different name.');
        }



        Role::create([
            "name" => $filteredRoleName,
            "created_by" => now(),
        ]);

        return redirect()->route('role.create')->withSuccess('Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {

        if (app()->isLocal()) {
            return to_route('role.index')->with('error', 'Role not updated in demo mode');
        }


        $request->validate([
            "role_title" => "required|string",
        ]);

        $filteredRoleName = strtolower($request->role_title);

        $role->update([
            "name" => $filteredRoleName,
            "updated_by" => now(),
        ]);

        return redirect()->route('role.create')->withSuccess('Role updated successfully.');
    }

    public function getPermission(Role $role)
    {
        $roles = Role::all();
        $roleId = $role->id;
        $permissions = config('acl.permissions');
        $rolePermission = $role->permissions->pluck('name')->toArray();
        return view('role.create', compact('roles', 'permissions', 'roleId', 'rolePermission'));
    }

    public function assignRoleToPermission(Request $request, Role $role)
    {
        if (app()->isLocal()) {
            return to_route('role.create')->with('error', 'Role not updated in demo mode');
        }

        if ($role->name == 'admin') {
            return redirect()->route('role.create')->withError('Admin role cannot be updated.');
        }
        $role->syncPermissions($request->permissions);
        return redirect()->route('role.create')->withSuccess('Role updated successfully.');
    }

    public function assignRoleToUser(Request $request, User $user)
    {
        $user->assignRole($request->role_name);
        return redirect()->route('role.index')->withSuccess('Assigned role successfully.');
    }

    public function removeRoleFromUser(Request $request, User $user, Role $role)
    {
        if (app()->isLocal()) {
            return to_route('role.index')->with('error', 'Role not updated in demo mode');
        }

        if ($user->hasRole($role->name)) {
            $user->removeRole($role->name);
        }
        return redirect()->route('role.index')->withSuccess('Removed role successfully.');
    }

    public function delete(Role $role)
    {
        if (app()->isLocal()) {
            return to_route('role.index')->with('error', 'Role not deleted in demo mode');
        }

        $role->delete();
        $role->permissions()->sync([]);
        return redirect()->route('role.create')->withSuccess('Role deleted successfully.');
    }
}
