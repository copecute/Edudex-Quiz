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
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * hiển thị danh sách user
     */
    public function index(Request $request)
    {
        $query = User::query()->with('userInfo');

        // Tìm kiếm theo username
        if ($search = $request->search) {
            $query->where('username', 'like', "%{$search}%");
        }

        // Lọc theo role
        if ($role = $request->role) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * hiển thị form tạo user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * xử lý tạo user
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:0,1,2',
            'fullName' => 'required',
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
            'role.required' => 'vai trò không được để trống',
            'role.in' => 'vai trò không hợp lệ',
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
                'role' => $request->role,
            ]);

            UserInfo::create([
                'user_id' => $user->id,
                'fullName' => $request->fullName,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'phoneNumber' => $request->phoneNumber,
                'address' => $request->address,
            ]);

            return redirect()->route('users.index')
                           ->with('success', 'tạo tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'đã có lỗi xảy ra khi tạo tài khoản');
        }
    }

    /**
     * hiển thị form chỉnh sửa user
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * xử lý cập nhật user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|unique:users,username,'.$user->id,
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|in:0,1,2',
            'fullName' => 'required',
            'gender' => 'required|boolean',
            'phoneNumber' => 'required',
            'address' => 'required',
        ], [
            'username.required' => 'username không được để trống',
            'username.unique' => 'username đã tồn tại',
            'email.required' => 'email không được để trống',
            'email.email' => 'email không đúng định dạng',
            'email.unique' => 'email đã tồn tại',
            'password.min' => 'mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'xác nhận mật khẩu không khớp',
            'role.required' => 'vai trò không được để trống',
            'role.in' => 'vai trò không hợp lệ',
            'fullName.required' => 'họ tên không được để trống',
            'gender.required' => 'giới tính không được để trống',
            'phoneNumber.required' => 'số điện thoại không được để trống',
            'address.required' => 'địa chỉ không được để trống',
        ]);

        try {
            $user->update([
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            if ($request->password) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $user->userInfo->update([
                'fullName' => $request->fullName,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'phoneNumber' => $request->phoneNumber,
                'address' => $request->address,
            ]);

            return redirect()->route('users.index')
                           ->with('success', 'cập nhật tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'đã có lỗi xảy ra khi cập nhật tài khoản');
        }
    }

    /**
     * xử lý xóa user
     */
    public function destroy(User $user)
    {
        try {
            $user->userInfo->delete();
            $user->delete();

            return redirect()->route('users.index')
                           ->with('success', 'xóa tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'đã có lỗi xảy ra khi xóa tài khoản');
        }
    }
} 