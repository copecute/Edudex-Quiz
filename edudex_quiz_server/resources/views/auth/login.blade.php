@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-4">
        <div class="card shadow-sm" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="logo" style="max-width: 200px;">
                </div>
                <h3 class="text-center mb-4">Đăng nhập</h3>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="loginButton" onclick="showLoading()">
                            Đăng nhập 
                        </button>
                    </div>
                    
                    <script>
                        function showLoading() {
                            const loginButton = document.getElementById('loginButton');
                            loginButton.innerHTML = '<i class="fa-solid fa-spinner-third fa-spin"></i>';
                            loginButton.disabled = true; // Vô hiệu hóa nút
                        document.forms[0].submit(); // gửi form sau khi hiển thị loading
                        }
                    </script>
            </div>
        </div>
    </div>
</div>
@endsection 