@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-rooms.index', $examPeriod) }}">
                            Phòng thi - {{ $examPeriod->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Nhập/Xuất</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Công cụ nhập/xuất phòng thi</h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Import Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Nhập danh sách phòng thi</h6>
                                    <p class="card-text text-muted small">
                                        Tải lên file Excel chứa danh sách phòng thi cần nhập.
                                        Vui lòng sử dụng mẫu được cung cấp.
                                    </p>
                                    <form action="{{ route('exam-period-rooms.import', $examPeriod) }}" 
                                          method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" class="form-control" name="file" required 
                                                   accept=".xlsx,.xls">
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('exam-period-rooms.template', $examPeriod) }}" 
                                               class="btn btn-outline-primary me-2">
                                                <i class="fas fa-download me-1"></i> Tải mẫu
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-1"></i> Nhập dữ liệu
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
                                    <h6 class="card-title">Xuất danh sách phòng thi</h6>
                                    <p class="card-text text-muted small">
                                        Tải xuống danh sách phòng thi hiện tại dưới dạng file Excel.
                                    </p>
                                    <a href="{{ route('exam-period-rooms.export', $examPeriod) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-download me-1"></i> Xuất dữ liệu
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 