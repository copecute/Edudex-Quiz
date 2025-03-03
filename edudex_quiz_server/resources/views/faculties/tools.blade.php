@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất khoa')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('faculties.index') }}">Quản lý khoa</a></li>
                    <li class="breadcrumb-item active">Nhập/Xuất</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        Công cụ nhập/xuất khoa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Import -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Nhập dữ liệu</h5>
                                    <p class="card-text">Nhập dữ liệu khoa từ file Excel.</p>
                                    <form action="{{ route('faculties.import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Chọn file Excel</label>
                                            <input type="file" class="form-control" id="file" name="file" required>
                                        </div>
                                        <div class="mb-3">
                                            <a href="{{ route('faculties.template') }}" class="btn btn-success">
                                                <i class="fas fa-download me-2"></i>Tải mẫu nhập
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>Nhập dữ liệu
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Export -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Xuất dữ liệu</h5>
                                    <p class="card-text">Xuất dữ liệu khoa ra file Excel.</p>
                                    <a href="{{ route('faculties.export') }}" class="btn btn-success">
                                        <i class="fas fa-download me-2"></i>Xuất dữ liệu
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('import_errors'))
                    <div class="alert alert-danger mt-3">
                        <h6 class="alert-heading">Lỗi import:</h6>
                        <ul class="mb-0">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 