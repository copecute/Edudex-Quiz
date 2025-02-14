@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chỉnh sửa tài khoản</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" 
                                       value="{{ old('username', $user->username) }}" 
                                       class="form-control" required>
                                @error('username')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       class="form-control" required>
                                @error('email')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                                <input type="password" name="password" class="form-control">
                                @error('password')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Vai trò</label>
                                <select name="role" class="form-select" required>
                                    <option value="0" {{ old('role', $user->role) === 0 ? 'selected' : '' }}>
                                        Quản trị viên
                                    </option>
                                    <option value="1" {{ old('role', $user->role) === 1 ? 'selected' : '' }}>
                                        Giáo viên
                                    </option>
                                    <option value="2" {{ old('role', $user->role) === 2 ? 'selected' : '' }}>
                                        Nhân viên
                                    </option>
                                </select>
                                @error('role')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="fullName" 
                                       value="{{ old('fullName', $user->userInfo->fullName) }}" 
                                       class="form-control" required>
                                @error('fullName')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" name="birthday" 
                                       value="{{ old('birthday', $user->userInfo->birthday) }}" 
                                       class="form-control">
                                @error('birthday')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Giới tính</label>
                                <select name="gender" class="form-select" required>
                                    <option value="1" {{ old('gender', $user->userInfo->gender) === 1 ? 'selected' : '' }}>
                                        Nam
                                    </option>
                                    <option value="0" {{ old('gender', $user->userInfo->gender) === 0 ? 'selected' : '' }}>
                                        Nữ
                                    </option>
                                </select>
                                @error('gender')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phoneNumber" 
                                       value="{{ old('phoneNumber', $user->userInfo->phoneNumber) }}" 
                                       class="form-control" required>
                                @error('phoneNumber')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="address" 
                                       value="{{ old('address', $user->userInfo->address) }}" 
                                       class="form-control" required>
                                @error('address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="{{ route('users.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
