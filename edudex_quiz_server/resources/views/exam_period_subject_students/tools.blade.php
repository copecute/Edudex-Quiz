@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất thí sinh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-subjects.index', $examPeriod) }}">
                            Môn thi - {{ $examPeriod->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-subject-students.index', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}">
                            Thí sinh - {{ $examPeriodSubject->subject->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Nhập/Xuất dữ liệu</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Công cụ nhập/xuất thí sinh</h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Import Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Nhập dữ liệu</h5>
                                    <p class="card-text">Nhập danh sách thí sinh từ file Excel.</p>
                                    
                                    <form action="{{ route('exam-period-subject-students.import', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                                          method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Chọn file Excel</label>
                                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                                   id="file" name="file" accept=".xlsx,.xls">
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('exam-period-subject-students.template', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                                               class="btn btn-secondary">
                                                <i class="fas fa-download me-1"></i> Tải mẫu
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-file-import me-1"></i> Nhập dữ liệu
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
                                    <h5 class="card-title">Xuất dữ liệu</h5>
                                    <p class="card-text">Tải xuống danh sách thí sinh dưới dạng Excel.</p>
                                    
                                    <a href="{{ route('exam-period-subject-students.export', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-file-export me-1"></i> Xuất Excel
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