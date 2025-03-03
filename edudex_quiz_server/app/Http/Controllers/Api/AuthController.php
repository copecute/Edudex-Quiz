<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

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

        // Kiểm tra thông tin đăng nhập
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thông tin đăng nhập không chính xác'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Kiểm tra tài khoản có bị khóa không
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản đã bị khóa'
            ], Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'info' => [
                    'full_name' => $user->accountInfo->fullName,
                    'date_of_birth' => $user->accountInfo->birthday,
                    'gender' => $user->accountInfo->gender,
                    'phone' => $user->accountInfo->phoneNumber,
                    'address' => $user->accountInfo->address,
                    'avatar' => $user->accountInfo->avatar,
                ]
            ]
        ]);
    }

    /**
     * Đăng xuất và xóa token
     */
    public function logout(Request $request)
    {
        try {
            // Xóa token hiện tại
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng xuất'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lấy thông tin người dùng đang đăng nhập
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'info' => [
                        'full_name' => $user->accountInfo->fullName,
                        'date_of_birth' => $user->accountInfo->birthday,
                        'gender' => $user->accountInfo->gender,
                        'phone' => $user->accountInfo->phoneNumber,
                        'address' => $user->accountInfo->address,
                        'avatar' => $user->accountInfo->avatar,
                    ]
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