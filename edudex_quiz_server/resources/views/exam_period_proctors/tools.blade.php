@extends('layouts.app')

@section('title', 'Công cụ nhập/xuất dữ liệu cán bộ coi thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-proctors.index', $examPeriod) }}">
                            Cán bộ coi thi - {{ $examPeriod->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Công cụ nhập/xuất</li>
                </ol>
            </nav>

            <div class="row">
                <!-- Import Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Nhập dữ liệu</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('exam-period-proctors.import', $examPeriod) }}" 
                                  method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="file" class="form-label">File Excel <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" required accept=".xlsx,.xls">
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Tải file mẫu <a href="{{ route('exam-period-proctors.template', $examPeriod) }}">tại đây</a>
                                    </div>
                                </div>

                                <div class="text-end">
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
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Xuất dữ liệu</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('exam-period-proctors.export', $examPeriod) }}" method="GET">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-file-export me-1"></i> Xuất Excel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 