@extends('layouts.app')

@section('title', 'Yêu cầu không hợp lệ')

@section('content')
<div class="container">
    <div class="row align-items-center">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="error-page">
                <div class="error-icon mb-4">
                    <h1 class="display-1 fw-bold text-primary mb-4">400</h1>
                </div>
                <h2 class="h4 mb-4">Yêu cầu không hợp lệ</h2>
                <p class="text-muted mb-4">
                    Yêu cầu của bạn không thể được xử lý. Vui lòng kiểm tra lại thông tin và thử lại.
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