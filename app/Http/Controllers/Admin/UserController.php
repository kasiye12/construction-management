<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('role')) {
            $query->where('role_id', function($q) use ($request) {
                $q->select('id')->from('roles')->where('name', $request->role);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        $user->load(['activityLogs' => function($q) { $q->latest()->take(50); }]);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:50|unique:users,username,'.$user->id,
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        if (empty($validated['password'])) unset($validated['password']);
        else $validated['password'] = Hash::make($validated['password']);
        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot delete yourself.');
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Status toggled.');
    }

    public function permissions(User $user)
    {
        $permissions = \App\Services\PermissionRegistry::getPermissionsGrouped();
        $hasCustom = $user->custom_permissions !== null;
        
        // Calculate inherited role permissions
        $roleName = $user->getRoleName();
        $role = $user->roleRelation ?? ($user->role_id ? Role::find($user->role_id) : null);
        if (!$role && !empty($user->role)) {
            $role = Role::where('name', $user->role)->first();
        }

        if ($role && !empty($role->permissions)) {
            $rolePerms = is_string($role->permissions) ? json_decode($role->permissions, true) : $role->permissions;
            $rolePermissions = is_array($rolePerms) ? $rolePerms : [];
        } else {
            $defaultMap = \App\Services\PermissionRegistry::getDefaultRolePermissions();
            $rolePermissions = $defaultMap[$roleName] ?? [];
        }

        // Active permissions for the checkboxes
        $userPermissions = $hasCustom ? ($user->custom_permissions ?? []) : $rolePermissions;

        return view('admin.users.permissions', compact('user', 'permissions', 'rolePermissions', 'userPermissions', 'hasCustom'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permission_mode' => 'required|in:inherit,custom',
            'permissions' => 'nullable|array',
            'is_super_admin' => 'nullable|boolean',
        ]);

        if ($validated['permission_mode'] === 'inherit') {
            $user->custom_permissions = null;
        } else {
            $user->custom_permissions = $request->input('permissions', []);
        }

        // Update super admin status if provided
        if ($request->has('is_super_admin')) {
            $user->is_super_admin = $request->boolean('is_super_admin');
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "Permissions updated successfully for {$user->name}.");
    }

    public function profile()
    {
        $user = Auth::user()->load(['activityLogs' => function($q) { $q->latest()->take(20); }]);
        return view('admin.users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:50|unique:users,username,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($validated);
        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed.');
    }
}
