@extends('layouts.app')

@section('title', 'Không có quyền truy cập')

@section('content')
<div class="container">
    <div class="row align-items-center">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-icon mb-4">
                    <h1 class="display-1 fw-bold text-primary mb-4">403</h1>
                </div>
                <h2 class="h4 mb-4">Không có quyền truy cập</h2>
                <p class="text-muted mb-4">
                    Bạn không có quyền truy cập vào trang này. Vui lòng liên hệ quản trị viên nếu bạn cần hỗ trợ.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="javascript:history.back()" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>
                        Trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 