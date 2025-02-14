<?php
//                       _oo0oo_
//                      o8888888o
//                      88" . "88
//                      (| -_- |)
//                      0\  =  /0
//                    ___/`---'\___
//                  .' \\|     |// '.
//                 / \\|||  :  |||// \
//                / _||||| -:- |||||- \
//               |   | \\\  -  /// |   |
//               | \_|  ''\---/''  |_/ |
//               \  .-\__  '-'  ___/-. /
//             ___'. .'  /--.--\  `. .'___
//          ."" '<  `.___\_<|>_/___.' >' "".
//         | | :  `- \`.;`\ _ /`;.`/ - ` : | |
//         \  \ `_.   \_ __\ /__ _/   .-` /  /
//     =====`-.____`.___ \_____/___.-`___.-'=====
//                       `=---='
//
//     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//            amen đà phật, không bao giờ BUG
//     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * xử lý đăng nhập api
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ], [
                'username.required' => 'username không được để trống',
                'password.required' => 'mật khẩu không được để trống',
            ]);

            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'thông tin đăng nhập không chính xác'
                ], 401);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'type' => 'success',
                'message' => 'đăng nhập thành công',
                'user' => $user,
                'token' => $token
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'type' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'có lỗi xảy ra, vui lòng thử lại'
            ], 500);
        }
    }

    /**
     * xử lý đăng xuất api
     */
    public function logout(Request $request)
    {
        try {
            // Lấy token từ request và xóa
            if ($token = $request->get('auth_token')) {
                $token->delete();
            }

            return response()->json([
                'type' => 'success',
                'message' => 'đăng xuất thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'có lỗi xảy ra, vui lòng thử lại'
            ], 500);
        }
    }

    /**
     * lấy thông tin user hiện tại
     */
    public function me(Request $request)
    {
        try {
            return response()->json([
                'type' => 'success',
                'message' => 'lấy thông tin thành công',
                'user' => $request->user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'có lỗi xảy ra, vui lòng thử lại'
            ], 500);
        }
    }
} 