@extends('layouts.app')

@section('title', 'Công cụ Import/Export Cơ sở')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('facilities.index') }}">Quản lý cơ sở thi</a></li>
                    <li class="breadcrumb-item active">Import/Export</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel text-primary me-2"></i>
                        Import/Export Cơ sở thi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Import Section -->
                        <div class="col-md-6 border-end">
                            <h6 class="text-primary">Import dữ liệu</h6>
                            <form action="{{ route('facilities.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Chọn file Excel</label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           name="file" accept=".xlsx, .xls">
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <a href="{{ route('facilities.template') }}" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-file-download me-2"></i>Tải file mẫu
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Import
                                    </button>
                                </div>
                            </form>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Hướng dẫn import</h6>
                                <ol class="mb-0 ps-3">
                                    <li>Tải file mẫu Excel</li>
                                    <li>Điền thông tin theo mẫu</li>
                                    <li>Trạng thái chỉ nhận giá trị "Hoạt động" hoặc "Khóa" (phân biệt chữ hoa/thường)</li>
                                    <li>Upload file và nhấn Import</li>
                                </ol>
                            </div>
                            @if(session('error'))
                            <div class="alert alert-danger mt-3">
                                <h6 class="alert-heading">{{ session('error') }}</h6>
                                @if(session('import_errors'))
                                    <ul class="mb-0">
                                        @foreach(session('import_errors') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Export Section -->
                        <div class="col-md-6">
                            <h6 class="text-primary">Export dữ liệu</h6>
                            <p class="text-muted">Tải xuống danh sách cơ sở thi dưới dạng file Excel.</p>
                            <div class="mb-3">
                                <a href="{{ route('facilities.export') }}" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Export Excel
                                </a>
                            </div>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Thông tin file export</h6>
                                <ul class="mb-0 ps-3">
                                    <li>Bao gồm tất cả cơ sở thi</li>
                                    <li>Định dạng .xlsx</li>
                                    <li>Có thể sử dụng để import</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 