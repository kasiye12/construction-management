<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProjectAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Admin sees everything
        if ($user && $user->isAdmin()) {
            return $next($request);
        }
        
        // Share user's projects globally
        if ($user) {
            $userProjects = $user->projects()->where('project_user.is_active', true)->get();
            view()->share('userProjects', $userProjects);
            view()->share('userProjectIds', $userProjects->pluck('id')->toArray());
        }
        
        return $next($request);
    }
}
