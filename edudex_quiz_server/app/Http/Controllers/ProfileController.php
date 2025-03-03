<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'email' => 'required|email|unique:accounts,email,' . $user->id,
            'fullName' => 'required|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'phoneNumber' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Cập nhật thông tin account
        $user->update([
            'email' => $request->email,
        ]);

        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->accountInfo->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->accountInfo->avatar);
            }
            
            // Upload avatar mới
            $avatar = $request->file('avatar');
            $filename = time() . '.' . $avatar->getClientOriginalExtension();
            
            // Lưu vào thư mục public/avatars
            $avatar->storeAs('avatars', $filename, 'public');
            
            // Cập nhật tên file trong database
            $user->accountInfo->avatar = $filename;
            $user->accountInfo->save();
        }

        // Cập nhật thông tin chi tiết
        $user->accountInfo->update([
            'fullName' => $request->fullName,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'phoneNumber' => $request->phoneNumber,
            'address' => $request->address,
        ]);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng!');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
} 