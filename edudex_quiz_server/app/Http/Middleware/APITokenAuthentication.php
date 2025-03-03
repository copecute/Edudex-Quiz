<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class APITokenAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $authorizationHeader = $request->header('Authorization');
        
        if ($authorizationHeader && str_starts_with($authorizationHeader, 'copecute ')) {
            $token = substr($authorizationHeader, strlen('copecute '));
            
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->tokenable) {
                // Lưu token vào request để sử dụng sau này
                $request->merge(['auth_token' => $accessToken]);
                Auth::login($accessToken->tokenable);
                return $next($request);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Không có quyền truy cập'
        ], 401);
    }
} 