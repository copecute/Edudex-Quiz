<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;
        
        // Kiểm tra role của user có được phép truy cập không
        foreach ($roles as $role) {
            // admin có thể truy cập tất cả
            if ($userRole == 2) return $next($request);
            
            // Giáo viên có thể truy cập role 1,0
            if ($userRole == 1 && in_array($role, [0,1])) return $next($request);
            
            // cán bộ coi thi chỉ có thể truy cập role 0
            if ($userRole == 0 && $role == 0) return $next($request);
        }

        abort(403, 'Không có quyền truy cập');
    }
} 