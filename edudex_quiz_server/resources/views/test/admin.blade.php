@extends('layouts.app')

@section('title', 'Test Admin Page')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Test Admin Page</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lock text-danger me-2"></i>
                        Trang dành cho Admin
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        Chỉ Admin (role 2) mới có thể xem trang này
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('test.teacher') }}" class="btn btn-primary me-2">Test Teacher Page</a>
                        <a href="{{ route('test.staff') }}" class="btn btn-primary">Test Staff Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 