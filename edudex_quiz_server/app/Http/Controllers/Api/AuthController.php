<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{
    /**
     * Đăng nhập và tạo token
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('username', 'password'))) {
            $user = Auth::user()->load('accountInfo');
            $token = $user->createToken('auth-token')->plainTextToken;

            $userInfo = [
                'full_name' => $user->accountInfo->fullName,
                'date_of_birth' => $user->accountInfo->birthday,
                'gender' => $user->accountInfo->gender,
                'phone' => $user->accountInfo->phoneNumber,
                'address' => $user->accountInfo->address,
                'avatar' => $user->accountInfo->avatar,
            ];

            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'token' => $token,
                    'user' => array_merge(
                        $userData,
                        ['info' => $userInfo]
                    )
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Thông tin đăng nhập không chính xác'
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                throw new AuthenticationException();
            }

            // Lấy token từ request
            $token = $request->auth_token;
            
            if (!$token) {
                throw new AuthenticationException('Token không hợp lệ');
            }

            // Xóa token
            $token->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chưa đăng nhập'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi đăng xuất'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lấy thông tin profile của user
     */
    public function profile(Request $request)
    {
        try {
            if (!$request->user()) {
                throw new AuthenticationException();
            }

            $user = $request->user()->load('accountInfo');
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => array_merge(
                        [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'role' => $user->role,
                        ],
                        ['info' => [
                            'full_name' => $user->accountInfo->fullName,
                            'date_of_birth' => $user->accountInfo->birthday,
                            'gender' => $user->accountInfo->gender,
                            'phone' => $user->accountInfo->phoneNumber,
                            'address' => $user->accountInfo->address,
                            'avatar' => $user->accountInfo->avatar,
                        ]]
                    )
                ]
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chưa đăng nhập'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
} 