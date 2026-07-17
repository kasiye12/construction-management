<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if user can perform action
     */
    public static function can(string $permission): bool
    {
        if (!Auth::check()) return false;
        return Auth::user()->hasPermission($permission);
    }

    /**
     * Check if user can view
     */
    public static function canView(string $module): bool
    {
        return self::can("{$module}.view");
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
     * Get permission buttons HTML
     */
    public static function actionButtons(string $module, $model, string $showRoute = null, string $editRoute = null, string $deleteRoute = null): string
    {
        $html = '<div class="btn-group btn-group-sm">';
        
        if (self::canView($module)) {
            $route = $showRoute ?? route("{$module}.show", $model);
            $html .= '<a href="' . $route . '" class="btn btn-info" title="View"><i class="fas fa-eye"></i></a>';
        }
        
        if (self::canEdit($module)) {
            $route = $editRoute ?? route("{$module}.edit", $model);
            $html .= '<a href="' . $route . '" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
        }
        
        if (self::canDelete($module)) {
            $route = $deleteRoute ?? route("{$module}.destroy", $model);
            $html .= '<form action="' . $route . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure?\')">';
            $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
            $html .= '<input type="hidden" name="_method" value="DELETE">';
            $html .= '<button type="submit" class="btn btn-danger" title="Delete"><i class="fas fa-trash"></i></button>';
            $html .= '</form>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Show create button if user has permission
     */
    public static function createButton(string $module, string $label = null, string $route = null): string
    {
        if (!self::canCreate($module)) return '';
        
        $route = $route ?? route("{$module}.create");
        $label = $label ?? 'Create New';
        
        return '<a href="' . $route . '" class="btn btn-primary"><i class="fas fa-plus me-1"></i> ' . $label . '</a>';
    }
}
