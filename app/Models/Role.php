<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 'display_name', 'description', 'permissions'
    ];

    protected $casts = [
        'permissions' => 'json'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->name === 'admin') {
            return true;
        }
        
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    public static function getDefaultPermissions(): array
    {
        return [
            'admin' => ['*'],
            'manager' => [
                'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
                'boq.view', 'boq.create', 'boq.edit', 'boq.delete',
                'ipc.view', 'ipc.create', 'ipc.approve',
                'subcontractors.view', 'subcontractors.create', 'subcontractors.edit',
                'cost-categories.view', 'cost-categories.create', 'cost-categories.edit',
                'reports.view', 'reports.export',
                'users.view',
            ],
            'engineer' => [
                'projects.view',
                'boq.view', 'boq.create', 'boq.edit',
                'ipc.view', 'ipc.create',
                'subcontractors.view',
                'cost-categories.view',
                'reports.view',
            ],
            'finance' => [
                'projects.view',
                'boq.view',
                'ipc.view', 'ipc.approve',
                'reports.view', 'reports.export',
            ],
            'viewer' => [
                'projects.view',
                'boq.view',
                'ipc.view',
                'reports.view',
            ],
        ];
    }
}
