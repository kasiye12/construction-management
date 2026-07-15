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
        'avatar', 'is_active', 'last_login_at', 'last_login_ip'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Role relationship
    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Activity logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Workflow permissions
    public function workflowPermissions()
    {
        return $this->hasMany(WorkflowPermission::class);
    }

    // Projects assigned to user
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withPivot(['role', 'assigned_date', 'end_date', 'is_active', 'responsibilities'])
                    ->withTimestamps();
    }

    // Get role name
    public function getRoleName(): string
    {
        if ($this->role_id) {
            $role = Role::find($this->role_id);
            if ($role) return $role->name;
        }
        if (!empty($this->attributes['role'])) {
            return $this->attributes['role'];
        }
        return 'viewer';
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->getRoleName(), ['admin', 'manager']);
    }

    public function isEngineer(): bool
    {
        return in_array($this->getRoleName(), ['admin', 'manager', 'engineer']);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        $roleName = $this->getRoleName();
        $role = Role::where('name', $roleName)->first();
        if ($role && $role->permissions) {
            $permissions = is_string($role->permissions) ? json_decode($role->permissions, true) : $role->permissions;
            if (is_array($permissions)) {
                return in_array('*', $permissions) || in_array($permission, $permissions);
            }
        }
        return $this->hasLegacyPermission($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) return true;
        }
        return false;
    }

    protected function hasLegacyPermission(string $permission): bool
    {
        $rolePermissions = [
            'admin' => ['*'],
            'manager' => ['projects.view','projects.create','projects.edit','projects.delete','boq.view','boq.create','boq.edit','boq.delete','ipc.view','ipc.create','ipc.approve','subcontractors.view','subcontractors.create','subcontractors.edit','cost-categories.view','cost-categories.create','cost-categories.edit','reports.view','reports.export','users.view','users.create','users.edit'],
            'engineer' => ['projects.view','boq.view','boq.create','boq.edit','ipc.view','ipc.create','subcontractors.view','cost-categories.view','reports.view'],
            'finance' => ['projects.view','boq.view','ipc.view','ipc.approve','reports.view','reports.export'],
            'viewer' => ['projects.view','boq.view','ipc.view','reports.view'],
        ];
        $role = $this->attributes['role'] ?? 'viewer';
        $perms = $rolePermissions[$role] ?? [];
        return in_array('*', $perms) || in_array($permission, $perms);
    }

    // Accessors
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
