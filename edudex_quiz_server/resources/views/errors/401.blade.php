@extends('layouts.app')

@section('title', 'Chưa đăng nhập')

@section('content')
<div class="container">
    <div class="row align-items-center">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-icon mb-4">
                    <h1 class="display-1 fw-bold text-primary mb-4">401</h1>
                </div>
                <h2 class="h4 mb-4">Bạn cần đăng nhập</h2>
                <p class="text-muted mb-4">
                    Vui lòng đăng nhập để truy cập trang này.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Đăng nhập
                    </a>
                    <a href="/" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i>
                        Trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 