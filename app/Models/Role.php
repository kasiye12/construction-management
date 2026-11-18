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
        return \App\Services\PermissionRegistry::getDefaultRolePermissions();
    }
}
