@extends('layouts.app')

@section('title', 'Test Teacher Page')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Test Teacher Page</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard-teacher text-success me-2"></i>
                        Trang dành cho Giáo viên
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        Admin (role 2) và Teacher (role 1) có thể xem trang này
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('test.admin') }}" class="btn btn-primary me-2">Test Admin Page</a>
                        <a href="{{ route('test.staff') }}" class="btn btn-primary">Test Staff Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 