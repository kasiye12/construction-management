<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckModulePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
