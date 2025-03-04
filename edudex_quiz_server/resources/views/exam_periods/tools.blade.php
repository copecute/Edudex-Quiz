@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất kỳ thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Import/Export</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Công cụ nhập/xuất kỳ thi</h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Import Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Import dữ liệu</h5>
                                    <p class="card-text">Nhập dữ liệu kỳ thi từ file Excel.</p>
                                    
                                    <form action="{{ route('exam-periods.import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Chọn file Excel</label>
                                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                                   id="file" name="file" accept=".xlsx, .xls" required>
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('exam-periods.template') }}" class="btn btn-secondary">
                                                <i class="fas fa-download me-1"></i> Tải template
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-file-import me-1"></i> Import
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Export Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Export dữ liệu</h5>
                                    <p class="card-text">Xuất dữ liệu kỳ thi ra file Excel.</p>
                                    
                                    <a href="{{ route('exam-periods.export') }}" class="btn btn-success">
                                        <i class="fas fa-file-export me-1"></i> Export Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="mt-4">
                        <h6>Lưu ý:</h6>
                        <ul class="mb-0">
                            <li>File import phải đúng định dạng Excel (.xlsx, .xls)</li>
                            <li>Dữ liệu trong file phải theo đúng template</li>
                            <li>Các trường đánh dấu (*) là bắt buộc</li>
                            <li>Định dạng thời gian: dd/mm/yyyy hh:mm (VD: 01/06/2025 07:00)</li>
                            <li>Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 