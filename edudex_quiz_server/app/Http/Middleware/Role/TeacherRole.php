<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;

class TeacherRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!in_array(auth()->user()->role, [1, 2])) {
            abort(403, 'Không có quyền truy cập');
        }
        return $next($request);
    }
} 