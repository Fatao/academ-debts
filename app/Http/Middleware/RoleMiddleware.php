<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($role === 'admin'   && $user->isAdmin())   return $next($request);
            if ($role === 'moderator'    && $user->isModerator())    return $next($request);
            if ($role === 'jobgiver' && $user->isJobgiver()) return $next($request);
            if ($role === 'freelancer' && $user->isFreelancer()) return $next($request);
        }

        abort(403, 'Доступ запрещён.');
    }
}
