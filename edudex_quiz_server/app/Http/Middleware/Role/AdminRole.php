<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;

class AdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->role != 2) {
            abort(403, 'Không có quyền truy cập');
        }
        return $next($request);
    }
} 