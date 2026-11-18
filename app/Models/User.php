<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'phone',
        'department', 'position', 'role', 'role_id',
        'avatar', 'is_active', 'last_login_at', 'last_login_ip',
        'custom_permissions', 'is_super_admin'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'custom_permissions' => 'array',
        'is_super_admin' => 'boolean',
    ];

    public function roleRelation() { return $this->belongsTo(Role::class, 'role_id'); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function workflowPermissions() { return $this->hasMany(WorkflowPermission::class); }
    
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withPivot(['role', 'assigned_date', 'end_date', 'is_active', 'responsibilities'])
                    ->withTimestamps();
    }

    public function getRoleName(): string
    {
        if ($this->role_id) {
            $role = Role::find($this->role_id);
            if ($role) return $role->name;
        }
        return $this->attributes['role'] ?? 'viewer';
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_super_admin || $this->isAdmin());
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === 'admin';
    }
    
    public function isManager(): bool { return in_array($this->getRoleName(), ['admin', 'manager']); }
    
    public function isEngineer(): bool { return in_array($this->getRoleName(), ['admin', 'manager', 'engineer']); }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // 1. Super Admin or Admin role has unrestricted access to all modules and documents
        if ($this->is_super_admin || $this->isAdmin()) {
            return true;
        }

        // 2. Check custom user-level permissions override if configured
        if ($this->custom_permissions !== null && is_array($this->custom_permissions)) {
            return in_array('*', $this->custom_permissions) || in_array($permission, $this->custom_permissions);
        }

        // 3. Check Role model permissions from DB relation or role name
        $role = $this->roleRelation ?? ($this->role_id ? Role::find($this->role_id) : null);
        if (!$role && !empty($this->role)) {
            $role = Role::where('name', $this->role)->first();
        }

        if ($role && !empty($role->permissions)) {
            $permissions = is_string($role->permissions) ? json_decode($role->permissions, true) : $role->permissions;
            if (is_array($permissions)) {
                return in_array('*', $permissions) || in_array($permission, $permissions);
            }
        }

        // 4. Fallback to default role permissions
        $roleName = $this->getRoleName();
        $defaultMap = \App\Services\PermissionRegistry::getDefaultRolePermissions();
        $perms = $defaultMap[$roleName] ?? [];
        return in_array('*', $perms) || in_array($permission, $perms);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin || $this->isAdmin()) {
            return true;
        }
        foreach ($permissions as $p) {
            if ($this->hasPermission($p)) return true;
        }
        return false;
    }

    /**
     * Check if user can access at least one permission in a module prefix
     */
    public function canAccessModule(string $modulePrefix): bool
    {
        if ($this->is_super_admin || $this->isAdmin()) {
            return true;
        }

        $effective = $this->getEffectivePermissions();
        if (in_array('*', $effective)) {
            return true;
        }

        foreach ($effective as $perm) {
            if ($perm === $modulePrefix || str_starts_with($perm, $modulePrefix . '.')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all effective permissions for this user
     */
    public function getEffectivePermissions(): array
    {
        if ($this->is_super_admin || $this->isAdmin()) {
            return ['*'];
        }

        // If user has custom permissions array set, return custom permissions
        if ($this->custom_permissions !== null && is_array($this->custom_permissions)) {
            return $this->custom_permissions;
        }

        // Role permissions
        $role = $this->roleRelation ?? ($this->role_id ? Role::find($this->role_id) : null);
        if (!$role && !empty($this->role)) {
            $role = Role::where('name', $this->role)->first();
        }

        if ($role && !empty($role->permissions)) {
            $rolePerms = is_string($role->permissions) ? json_decode($role->permissions, true) : $role->permissions;
            if (is_array($rolePerms)) {
                return $rolePerms;
            }
        }

        $roleName = $this->getRoleName();
        $defaultMap = \App\Services\PermissionRegistry::getDefaultRolePermissions();
        return $defaultMap[$roleName] ?? [];
    }


    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        return strtoupper(substr($words[0] ?? 'U', 0, 1));
    }

    public function getRoleLabelAttribute(): string
    {
        $roleName = $this->getRoleName();
        $role = Role::where('name', $roleName)->first();
        return $role ? $role->display_name : ucfirst($roleName);
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
