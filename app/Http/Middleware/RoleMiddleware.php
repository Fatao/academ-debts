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
            if ($role === 'dean'    && $user->isDean())    return $next($request);
            if ($role === 'teacher' && $user->isTeacher()) return $next($request);
            if ($role === 'student' && $user->isStudent()) return $next($request);
        }

        abort(403, 'Доступ запрещён.');
    }
}