<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    /**
     * Check if current user can perform action
     */
    public static function can(string $permission): bool
    {
        if (!Auth::check()) return false;
        return Auth::user()->hasPermission($permission);
    }

    /**
     * Check if user can create
     */
    public static function canCreate(string $module): bool
    {
        return self::can("{$module}.create");
    }

    /**
     * Check if user can edit
     */
    public static function canEdit(string $module): bool
    {
        return self::can("{$module}.edit");
    }

    /**
     * Check if user can delete
     */
    public static function canDelete(string $module): bool
    {
        return self::can("{$module}.delete");
    }

    /**
     * Get user role badge HTML
     */
    public static function getRoleBadge($user): string
    {
        $roleName = $user->role?->name ?? 'user';
        $roleLabel = $user->role_label ?? 'User';
        
        $colors = [
            'admin' => 'danger',
            'manager' => 'primary',
            'engineer' => 'success',
            'finance' => 'warning',
            'viewer' => 'secondary',
        ];
        
        $color = $colors[$roleName] ?? 'secondary';
        
        return "<span class=\"badge bg-{$color} badge-status\">{$roleLabel}</span>";
    }

    /**
     * Get permission list for display
     */
    public static function getPermissionGroups(): array
    {
        return \App\Services\PermissionRegistry::getPermissionsGrouped();
    }

    /**
     * Get readable permission label
     */
    public static function getPermissionLabel(string $permission): string
    {
        foreach (\App\Services\PermissionRegistry::getPermissionsGrouped() as $group => $perms) {
            if (isset($perms[$permission])) {
                return $perms[$permission];
            }
        }
        $parts = explode('.', $permission);
        $module = ucfirst(str_replace('-', ' ', $parts[0]));
        $action = ucfirst($parts[1] ?? '');
        return "{$action} {$module}";
    }
}
