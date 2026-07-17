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

    public function isAdmin(): bool { return $this->getRoleName() === 'admin'; }
    
    public function isManager(): bool { return in_array($this->getRoleName(), ['admin', 'manager']); }
    
    public function isEngineer(): bool { return in_array($this->getRoleName(), ['admin', 'manager', 'engineer']); }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->isAdmin()) return true;
        
        // Check Role model permissions
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
        foreach ($permissions as $p) { if ($this->hasPermission($p)) return true; }
        return false;
    }

    protected function hasLegacyPermission(string $permission): bool
    {
        $rolePermissions = [
            'admin' => ['*'],
            'manager' => [
                'projects.view','projects.create','projects.edit','projects.delete',
                'boq.view','boq.create','boq.edit','boq.delete',
                'ipc.view','ipc.create','ipc.edit','ipc.approve','ipc.delete',
                'subcontractors.view','subcontractors.create','subcontractors.edit','subcontractors.delete',
                'cost-categories.view','cost-categories.create','cost-categories.edit','cost-categories.delete',
                'reports.view','reports.export',
                'actual-costs.view','actual-costs.create','actual-costs.edit','actual-costs.delete',
                'users.view','users.create','users.edit',
                'roles.view','roles.create','roles.edit',
            ],
            'engineer' => [
                'projects.view',
                'boq.view','boq.create','boq.edit',
                'ipc.view','ipc.create','ipc.edit',
                'subcontractors.view',
                'cost-categories.view',
                'reports.view',
                'actual-costs.view','actual-costs.create',
            ],
            'finance' => [
                'projects.view',
                'boq.view',
                'ipc.view','ipc.approve',
                'reports.view','reports.export',
                'actual-costs.view',
            ],
            'viewer' => [
                'projects.view','boq.view','ipc.view','reports.view',
            ],
        ];
        
        $role = $this->attributes['role'] ?? 'viewer';
        $perms = $rolePermissions[$role] ?? [];
        return in_array('*', $perms) || in_array($permission, $perms);
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
