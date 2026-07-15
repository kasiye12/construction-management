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
        return [
            'Projects' => [
                'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            ],
            'BOQ & Costing' => [
                'boq.view', 'boq.create', 'boq.edit', 'boq.delete',
            ],
            'IPCs & Payments' => [
                'ipc.view', 'ipc.create', 'ipc.edit', 'ipc.approve', 'ipc.delete',
            ],
            'Subcontractors' => [
                'subcontractors.view', 'subcontractors.create', 'subcontractors.edit', 'subcontractors.delete',
            ],
            'Cost Categories' => [
                'cost-categories.view', 'cost-categories.create', 'cost-categories.edit', 'cost-categories.delete',
            ],
            'Reports' => [
                'reports.view', 'reports.export',
            ],
            'Users & Roles' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            ],
        ];
    }
}
