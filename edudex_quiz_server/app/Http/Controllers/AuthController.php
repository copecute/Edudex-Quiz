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
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * hiển thị form đăng nhập
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ], [
            'username.required' => 'username không được để trống',
            'password.required' => 'mật khẩu không được để trống'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard')
                           ->with('success', 'đăng nhập thành công!');
        }

        return back()->withErrors([
            'username' => 'thông tin đăng nhập không chính xác.',
        ])->withInput($request->only('username'));
    }

    /**
     * hiển thị form đăng ký
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * xử lý đăng ký
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'fullName' => 'required',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'phoneNumber' => 'required',
            'address' => 'required',
        ], [
            'username.required' => 'username không được để trống',
            'username.unique' => 'username đã tồn tại',
            'email.required' => 'email không được để trống',
            'email.email' => 'email không đúng định dạng',
            'email.unique' => 'email đã tồn tại',
            'password.required' => 'mật khẩu không được để trống',
            'password.min' => 'mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'xác nhận mật khẩu không khớp',
            'fullName.required' => 'họ tên không được để trống',
            'gender.required' => 'giới tính không được để trống',
            'phoneNumber.required' => 'số điện thoại không được để trống',
            'address.required' => 'địa chỉ không được để trống',
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 2, // mặc định là nhân viên
            ]);

            UserInfo::create([
                'user_id' => $user->id,
                'fullName' => $request->fullName,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'phoneNumber' => $request->phoneNumber,
                'address' => $request->address,
            ]);

            Auth::login($user);
            return redirect()->route('dashboard')
                           ->with('success', 'đăng ký tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'đã có lỗi xảy ra khi đăng ký. vui lòng thử lại.');
        }
    }

    /**
     * xử lý đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'đăng xuất thành công!');
    }
} 