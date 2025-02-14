<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Đăng ký</div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" 
                                       value="{{ old('username') }}" required>
                                @error('username')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Mật khẩu</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Xác nhận mật khẩu</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Họ và tên</label>
                                <input type="text" name="fullName" class="form-control @error('fullName') is-invalid @enderror" 
                                       value="{{ old('fullName') }}" required>
                                @error('fullName')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Ngày sinh</label>
                                <input type="date" name="birthday" class="form-control @error('birthday') is-invalid @enderror" 
                                       value="{{ old('birthday') }}">
                                @error('birthday')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Giới tính</label>
                                <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                    <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Nam</option>
                                    <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                                </select>
                                @error('gender')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Số điện thoại</label>
                                <input type="text" name="phoneNumber" class="form-control @error('phoneNumber') is-invalid @enderror" 
                                       value="{{ old('phoneNumber') }}" required>
                                @error('phoneNumber')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label>Địa chỉ</label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" 
                                       value="{{ old('address') }}" required>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Đăng ký</button>
                            <a href="{{ route('login') }}" class="btn btn-link">Đã có tài khoản? Đăng nhập</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 