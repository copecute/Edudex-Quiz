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

            <!-- Header Card -->
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

            <!-- Search Form -->
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

            <!-- Exam Periods Grid -->
            <div class="row g-4">
                @forelse ($examPeriods as $examPeriod)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $examPeriod->name }}</h6>
                            @php
                                $now = now();
                                $status = '';
                                $statusClass = '';
                                
                                if (!$examPeriod->is_active) {
                                    $status = 'Đã khóa';
                                    $statusClass = 'bg-secondary';
                                } else if ($now->between($examPeriod->start_time, $examPeriod->end_time)) {
                                    $status = 'Đang diễn ra';
                                    $statusClass = 'bg-success';
                                } else if ($now->lt($examPeriod->start_time)) {
                                    $daysLeft = $now->diffInDays($examPeriod->start_time);
                                    $status = "Sắp diễn ra (còn {$daysLeft} ngày)";
                                    $statusClass = 'bg-info text-dark';
                                } else {
                                    $status = 'Đã kết thúc';
                                    $statusClass = 'bg-danger';
                                }
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $status }}</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Thời gian bắt đầu:</small>
                                <span><i class="fas fa-calendar-alt me-1"></i> {{ $examPeriod->start_time->format('d/m/Y') }}</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Thời gian kết thúc:</small>
                                <span><i class="fas fa-calendar-alt me-1"></i> {{ $examPeriod->end_time->format('d/m/Y') }}</span>
                            </div>
                            @if($examPeriod->description)
                            <div class="mb-3">
                                <small class="text-muted d-block">Mô tả:</small>
                                <span>{{ $examPeriod->description }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between">
                                <!-- Dropdown cho các chức năng quản lý -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i> Quản lý
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('exam-shifts.index', $examPeriod) }}">
                                                <i class="fas fa-clock me-2"></i> Ca thi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('exam-period-rooms.index', $examPeriod) }}">
                                                <i class="fas fa-door-open me-2"></i> Phòng thi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('exam-period-subjects.index', $examPeriod) }}">
                                                <i class="fas fa-book me-2"></i> Môn thi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('exam-period-proctors.index', $examPeriod) }}">
                                                <i class="fas fa-user-tie me-2"></i> Cán bộ coi thi
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Dropdown cho các thao tác chỉnh sửa -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if($examPeriod->is_active)
                                        <li>
                                            <form action="{{ route('exam-periods.toggle-status', $examPeriod) }}" 
                                                  method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="fas fa-lock me-2"></i> Khóa kỳ thi
                                                </button>
                                            </form>
                                        </li>
                                        @else
                                        <li>
                                            <form action="{{ route('exam-periods.toggle-status', $examPeriod) }}" 
                                                  method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-lock-open me-2"></i> Mở khóa kỳ thi
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="{{ route('exam-periods.edit', $examPeriod) }}">
                                                <i class="fas fa-edit me-2"></i> Chỉnh sửa
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('exam-periods.destroy', $examPeriod) }}" 
                                                  method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i> Xóa
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="mb-0">Không có dữ liệu</p>
                        </div>
                    </div>
                </div>
                @endforelse
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