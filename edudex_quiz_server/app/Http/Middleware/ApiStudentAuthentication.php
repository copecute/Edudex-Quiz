<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiStudentAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization', '');
        
        if (!str_starts_with($header, 'copecute ')) {
            return response()->json([
                'message' => 'Unauthorized - Invalid token prefix'
            ], 401);
        }

        $token = str_replace('copecute ', '', $header);

        if (empty($token)) {
            return response()->json([
                'message' => 'Unauthorized - Token not provided'
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable instanceof \App\Models\Student) {
            return response()->json([
                'message' => 'Unauthorized - Invalid token'
            ], 401);
        }

        // Kiểm tra student có đang học không
        if (!$accessToken->tokenable->status) {
            return response()->json([
                'message' => 'Unauthorized - Student is inactive'
            ], 401);
        }

        // Set user cho request
        $request->setUserResolver(function () use ($accessToken) {
            return $accessToken->tokenable;
        });

        return $next($request);
    }
} 