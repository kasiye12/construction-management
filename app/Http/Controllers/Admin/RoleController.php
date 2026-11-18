<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = $this->getAllPermissions();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name|alpha_dash',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        $validated['permissions'] = $request->permissions ?? [];
        
        Role::create($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('users');
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = $this->getAllPermissions();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        $validated['permissions'] = $request->permissions ?? [];
        
        $role->update($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Cannot delete admin role.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    private function getAllPermissions(): array
    {
        return \App\Services\PermissionRegistry::getPermissionsGrouped();
    }
}
