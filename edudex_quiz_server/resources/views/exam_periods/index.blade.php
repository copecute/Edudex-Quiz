@extends('layouts.app')

@section('title', 'Quản lý kỳ thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kỳ thi</li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách kỳ thi</h5>
                    <div>
                        <a href="{{ route('exam-periods.tools') }}" class="btn btn-secondary">
                            <i class="fas fa-file-import me-1"></i> Import/Export
                        </a>
                        <a href="{{ route('exam-periods.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm mới
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('exam-periods.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Tìm theo tên kỳ thi..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>
                                        Đang diễn ra
                                    </option>
                                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>
                                        Sắp diễn ra
                                    </option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                        Đã kết thúc
                                    </option>
                                    <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>
                                        Đã khóa
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="start_date" 
                                       placeholder="Từ ngày" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="end_date" 
                                       placeholder="Đến ngày" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 30%">Tên kỳ thi</th>
                                    <th style="width: 15%">Thời gian</th>
                                    <th style="width: 15%">Thời gian diễn ra</th>
                                    <th style="width: 20%">Mô tả</th>
                                    <th style="width: 15%">Trạng thái</th>
                                    <th style="width: 15%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($examPeriods as $examPeriod)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $examPeriod->name }}</td>
                                    <td>{{ $examPeriod->start_time->format('d/m/Y') }} - {{ $examPeriod->end_time->format('d/m/Y') }}</td>
                                    <td>{{ ceil($examPeriod->start_time->diffInDays($examPeriod->end_time)) }} ngày</td>
                                    <td>{{ Str::limit($examPeriod->description, 40) }}</td>
                                    <td>
                                        @php
                                            $now = now();
                                            $status = '';
                                            $statusClass = '';
                                            
                                            if (!$examPeriod->is_active) {
                                                $status = 'Đã khóa';
                                                $statusClass = 'bg-secondary';
                                            } else if ($now->between($examPeriod->start_time, $examPeriod->end_time)) {
                                                $daysLeft = $now->floatDiffInDays($examPeriod->end_time);
                                                if ($daysLeft < 1) {
                                                    $daysLeft = 1;
                                                } else {
                                                    $daysLeft = ceil($daysLeft);
                                                }
                                                $status = "Đang diễn ra (còn {$daysLeft} ngày)";
                                                $statusClass = 'bg-success';
                                            } else if ($now->lt($examPeriod->start_time)) {
                                                $daysLeft = $now->floatDiffInDays($examPeriod->start_time);
                                                if ($daysLeft < 1) {
                                                    $daysLeft = 1;
                                                } else {
                                                    $daysLeft = ceil($daysLeft);
                                                }
                                                $status = "Sắp diễn ra (còn {$daysLeft} ngày)";
                                                $statusClass = 'bg-info text-dark';
                                            } else {
                                                $status = 'Đã kết thúc';
                                                $statusClass = 'bg-danger';
                                            }
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('exam-periods.dashboard', $examPeriod) }}" class="btn btn-success btn-sm me-2">
                                                <i class="fas fa-dashboard"></i>
                                            </a>
                                            @if($examPeriod->is_active)
                                            <form action="{{ route('exam-periods.toggle-status', $examPeriod) }}" 
                                                  method="POST" class="me-2">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </form>
                                            @else
                                            <form action="{{ route('exam-periods.toggle-status', $examPeriod) }}" 
                                                  method="POST" class="me-2">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-lock-open"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <a class="btn btn-primary btn-sm me-2" href="{{ route('exam-periods.edit', $examPeriod) }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('exam-periods.destroy', $examPeriod) }}" 
                                                  method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end mt-4">
                {{ $examPeriods->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Xác nhận xóa
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn xóa kỳ thi này? Tất cả dữ liệu liên quan sẽ bị xóa và không thể khôi phục.')) {
            this.submit();
        }
    });

    // Enable tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});

function clearFilters() {
    window.location.href = "{{ route('exam-periods.index') }}";
}
</script>
@endpush 