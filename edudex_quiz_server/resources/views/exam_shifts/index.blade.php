@extends('layouts.app')

@section('title', 'Quản lý ca thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Ca thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách ca thi</h5>
                    <a href="{{ route('exam-shifts.create', $examPeriod) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('exam-shifts.index', $examPeriod) }}" method="GET" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Tìm kiếm..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Khóa</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 25%">Tên ca thi</th>
                                    <th style="width: 20%">Ngày thi</th>
                                    <th style="width: 25%">Thời gian</th>
                                    <th style="width: 15%">Trạng thái</th>
                                    <th style="width: 10%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shifts as $examShift)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $examShift->name }}
                                        @if ($examShift->description)
                                            <i class="fas fa-info-circle text-info" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ $examShift->description }}"></i>
                                        @endif
                                    </td>
                                    <td>{{ $examShift->start_time->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $examShift->start_time->format('H:i') }} - {{ $examShift->end_time->format('H:i') }}
                                        <div class="small text-muted">
                                            ({{ $examShift->start_time->diffInMinutes($examShift->end_time) }} phút)
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $now = now();
                                            $status = '';
                                            $statusClass = '';
                                            
                                            if (!$examShift->is_active) {
                                                $status = 'Đã khóa';
                                                $statusClass = 'bg-secondary';
                                            } else if ($now->between($examShift->start_time, $examShift->end_time)) {
                                                $minutesLeft = ceil($now->diffInMinutes($examShift->end_time));
                                                $status = "Đang diễn ra (còn {$minutesLeft} phút)";
                                                $statusClass = 'bg-success';
                                            } else if ($now->lt($examShift->start_time)) {
                                                $diffInMinutes = $now->diffInMinutes($examShift->start_time);
                                                
                                                if ($diffInMinutes >= 1440) { // >= 24 giờ
                                                    $days = floor($diffInMinutes / 1440);
                                                    $status = "Sắp diễn ra (còn {$days} ngày)";
                                                } else if ($diffInMinutes >= 60) { // >= 1 giờ
                                                    $hours = floor($diffInMinutes / 60);
                                                    $status = "Sắp diễn ra (còn {$hours} giờ)";
                                                } else {
                                                    $diffInMinutes = ceil($diffInMinutes);
                                                    $status = "Sắp diễn ra (còn {$diffInMinutes} phút)";
                                                }
                                                $statusClass = 'bg-primary';
                                            } else {
                                                $status = 'Đã kết thúc';
                                                $statusClass = 'bg-danger';
                                            }
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('exam-shifts.toggle-status', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm status-btn {{ $examShift->is_active ? 'btn-success' : 'btn-danger' }}">
                                                @if($examShift->is_active)
                                                    <i class="fas fa-lock-open"></i>
                                                @else
                                                    <i class="fas fa-lock"></i>
                                                @endif
                                            </button>
                                        </form>
                                        <a href="{{ route('exam-shifts.edit', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('exam-shifts.destroy', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" 
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có ca thi nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $shifts->links() }}
                    </div>
                </div>
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
        if (confirm('Bạn có chắc chắn muốn xóa?')) {
            this.submit();
        }
    });

    // Enable tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush 