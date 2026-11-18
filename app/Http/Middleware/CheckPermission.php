<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Supports single permission or comma-separated permissions ('permission:database.backup,database.sync').
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Expand any comma-separated permissions in arguments
        $required = [];
        foreach ($permissions as $p) {
            foreach (explode(',', (string)$p) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $required[] = $trimmed;
                }
            }
        }

        if (empty($required)) {
            return $next($request);
        }

        if (!auth()->user()->hasAnyPermission($required)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

