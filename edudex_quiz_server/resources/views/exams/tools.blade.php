@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất đề thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Quản lý đề thi</a></li>
                    <li class="breadcrumb-item active">Nhập/Xuất</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        Công cụ nhập/xuất đề thi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Import Section -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Import từ Excel</h6>
                            <form action="{{ route('exams.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="file" accept=".xlsx,.xls" required>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Import Excel
                                    </button>
                                    <a href="{{ route('exams.template') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-download me-2"></i>Tải template
                                    </a>
                                </div>
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Hướng dẫn import</h6>
                                    <ul class="mb-0 ps-3">
                                        <li>Sử dụng template được cung cấp</li>
                                        <li>Điền đầy đủ các trường bắt buộc</li>
                                        <li>Định dạng tags: tag_id|num_questions|easy_rate|medium_rate|hard_rate</li>
                                        <li>Các tags cách nhau bởi dấu chấm phẩy (;)</li>
                                    </ul>
                                </div>
                            </form>
                        </div>

                        <!-- Export Section -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Export ra Excel</h6>
                            <div class="mb-3">
                                <a href="{{ route('exams.export') }}" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Export Excel
                                </a>
                            </div>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Thông tin file export</h6>
                                <ul class="mb-0 ps-3">
                                    <li>Bao gồm tất cả đề thi</li>
                                    <li>Kèm thông tin tags</li>
                                    <li>Định dạng .xlsx</li>
                                    <li>Có thể sử dụng để import</li>
                                </ul>
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