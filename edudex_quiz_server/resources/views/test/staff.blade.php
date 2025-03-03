@extends('layouts.app')

@section('title', 'Test Staff Page')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Test Staff Page</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-info me-2"></i>
                        Trang dành cho CBCT
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Tất cả người dùng đã đăng nhập đều có thể xem trang này
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('test.admin') }}" class="btn btn-primary me-2">Test Admin Page</a>
                        <a href="{{ route('test.teacher') }}" class="btn btn-primary">Test Teacher Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 