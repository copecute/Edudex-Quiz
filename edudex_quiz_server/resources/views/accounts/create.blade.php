@extends('layouts.app')

@section('title', 'Thêm tài khoản')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accounts.index') }}">Quản lý tài khoản</a></li>
                    <li class="breadcrumb-item active">Thêm tài khoản</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        Thêm tài khoản mới
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('accounts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Thông tin đăng nhập -->
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Thông tin đăng nhập</h6>
                                <div class="mb-3">
                                    <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                           name="username" value="{{ old('username') }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                                        <option value="">Chọn vai trò</option>
                                        <option value="0" {{ old('role') == '0' ? 'selected' : '' }}>CBCT</option>
                                        <option value="1" {{ old('role') == '1' ? 'selected' : '' }}>Giáo viên</option>
                                        <option value="2" {{ old('role') == '2' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Thông tin cá nhân -->
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Thông tin cá nhân</h6>
                                <div class="mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('fullName') is-invalid @enderror" 
                                           name="fullName" value="{{ old('fullName') }}" required>
                                    @error('fullName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" class="form-control @error('birthday') is-invalid @enderror" 
                                           name="birthday" value="{{ old('birthday') }}">
                                    @error('birthday')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Giới tính <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" 
                                                   id="gender1" value="1" {{ old('gender') == '1' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="gender1">Nam</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" 
                                                   id="gender0" value="0" {{ old('gender') == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gender0">Nữ</label>
                                        </div>
                                    </div>
                                    @error('gender')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phoneNumber') is-invalid @enderror" 
                                           name="phoneNumber" value="{{ old('phoneNumber') }}" required>
                                    @error('phoneNumber')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              name="address" rows="2" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <a href="{{ route('accounts.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 