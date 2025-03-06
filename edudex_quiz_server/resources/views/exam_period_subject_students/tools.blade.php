@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất thí sinh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('students.index', $examPeriod) }}">Quản lý thí sinh</a></li>
                    <li class="breadcrumb-item active">Nhập/Xuất</li>
                </ol>
            </nav>

            <div class="row">
                <!-- Import Card -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Import thí sinh</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('students.import', $examPeriod) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Chọn môn thi</label>
                                    <select name="subject_id" class="form-select" required>
                                        <option value="">-- Chọn môn thi --</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">
                                                {{ $subject->subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">File Excel</label>
                                    <input type="file" name="file" class="form-control" required 
                                           accept=".xlsx,.xls">
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('students.template', $examPeriod) }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-download me-1"></i> Tải mẫu
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i> Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Export Card -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Export thí sinh</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('students.export', $examPeriod) }}" method="GET">
                                <div class="mb-3">
                                    <label class="form-label">Môn thi</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">Tất cả môn thi</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">
                                                {{ $subject->subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 