@extends('layouts.app')

@section('title', 'Dashboard kỳ thi - ' . $examPeriod->name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">{{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <!-- Thông tin kỳ thi -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">{{ $examPeriod->name }}</h4>
                        @php
                            $now = now();
                            $status = '';
                            $statusClass = '';
                            
                            if (!$examPeriod->is_active) {
                                $status = 'Đã khóa';
                                $statusClass = 'bg-secondary';
                            } else if ($now->between($examPeriod->start_time, $examPeriod->end_time)) {
                                $diffInMinutes = $now->diffInMinutes($examPeriod->end_time);
                                $days = ceil($diffInMinutes / 1440);
                                $status = "Đang diễn ra (còn {$days} ngày)";
                                $statusClass = 'bg-success';
                            } else if ($now->lt($examPeriod->start_time)) {
                                $diffInMinutes = $now->diffInMinutes($examPeriod->start_time);
                                $days = ceil($diffInMinutes / 1440);
                                $status = "Sắp diễn ra (còn {$days} ngày)";
                                $statusClass = 'bg-primary';
                            } else {
                                $status = 'Đã kết thúc';
                                $statusClass = 'bg-danger';
                            }
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <span class="description-text" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $examPeriod->description ?? 'Không có mô tả' }}
                                </span>
                                @if(strlen($examPeriod->description) > 200)
                                    <button class="btn btn-link btn-sm p-0 ms-2 show-more">
                                        <i class="fas fa-chevron-down me-1"></i>Xem thêm
                                    </button>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Bắt đầu: {{ $examPeriod->start_time->format('d/m/Y') }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Kết thúc: {{ $examPeriod->end_time->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Ca thi</h6>
                            <h2 class="mb-0">{{ $stats['total_shifts'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Môn thi</h6>
                            <h2 class="mb-0">{{ $stats['total_subjects'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Thí sinh</h6>
                            <h2 class="mb-0">{{ $stats['total_students'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Phòng thi</h6>
                            <h2 class="mb-0">{{ $stats['total_rooms'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title">Cán bộ coi thi</h6>
                            <h2 class="mb-0">{{ $stats['total_proctors'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách ca thi -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ca thi sắp diễn ra</h5>
                    <a href="{{ route('exam-shifts.index', $examPeriod) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list me-1"></i>Xem tất cả
                    </a>
                </div>
                <div class="card-body">
                    @if($examPeriod->examShifts->isEmpty())
                        <p class="text-muted mb-0">Chưa có ca thi nào</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 25%">Tên ca thi</th>
                                        <th style="width: 20%">Ngày thi</th>
                                        <th style="width: 25%">Thời gian</th>
                                        <th style="width: 25%">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examPeriod->examShifts->take(3) as $shift)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $shift->name }}</td>
                                            <td>{{ $shift->start_time->format('d/m/Y') }}</td>
                                            <td>
                                                {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                                <div class="small text-muted">
                                                    ({{ $shift->start_time->diffInMinutes($shift->end_time) }} phút)
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $now = now();
                                                    $status = '';
                                                    $statusClass = '';
                                                    
                                                    if (!$shift->is_active) {
                                                        $status = 'Đã khóa';
                                                        $statusClass = 'bg-secondary';
                                                    } else if ($now->between($shift->start_time, $shift->end_time)) {
                                                        $minutesLeft = ceil($now->diffInMinutes($shift->end_time));
                                                        $status = "Đang diễn ra (còn {$minutesLeft} phút)";
                                                        $statusClass = 'bg-success';
                                                    } else if ($now->lt($shift->start_time)) {
                                                        $diffInMinutes = $now->diffInMinutes($shift->start_time);
                                                        
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($examPeriod->examShifts->count() > 3)
                            <div class="collapse" id="moreShifts">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        @foreach($examPeriod->examShifts->slice(3) as $shift)
                                            <tr>
                                                <td style="width: 5%">{{ $loop->iteration + 3 }}</td>
                                                <td style="width: 25%">{{ $shift->name }}</td>
                                                <td style="width: 20%">{{ $shift->start_time->format('d/m/Y') }}</td>
                                                <td style="width: 25%">
                                                    {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                                    <div class="small text-muted">
                                                        ({{ $shift->start_time->diffInMinutes($shift->end_time) }} phút)
                                                    </div>
                                                </td>
                                                <td style="width: 25%">
                                                    @php
                                                        $now = now();
                                                        $status = '';
                                                        $statusClass = '';
                                                        
                                                        if (!$shift->is_active) {
                                                            $status = 'Đã khóa';
                                                            $statusClass = 'bg-secondary';
                                                        } else if ($now->between($shift->start_time, $shift->end_time)) {
                                                            $minutesLeft = ceil($now->diffInMinutes($shift->end_time));
                                                            $status = "Đang diễn ra (còn {$minutesLeft} phút)";
                                                            $statusClass = 'bg-success';
                                                        } else if ($now->lt($shift->start_time)) {
                                                            $diffInMinutes = $now->diffInMinutes($shift->start_time);
                                                            
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
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center mt-3">
                                <button class="btn btn-link btn-sm" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#moreShifts" 
                                        aria-expanded="false">
                                    <span class="more-text">
                                        <i class="fas fa-chevron-down me-1"></i>
                                        Xem thêm {{ $examPeriod->examShifts->count() - 3 }} ca thi
                                    </span>
                                    <span class="less-text d-none">
                                        <i class="fas fa-chevron-up me-1"></i>
                                        Thu gọn
                                    </span>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Các nút thao tác nhanh -->
            <div class="row g-4">
                <div class="col-md-3">
                    <a href="{{ route('exam-shifts.index', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>
                                Quản lý ca thi
                            </h5>
                            <p class="card-text text-muted">
                                Thêm, sửa, xóa các ca thi trong kỳ thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-period-subjects.index', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-book me-2"></i>
                                Quản lý môn thi
                            </h5>
                            <p class="card-text text-muted">
                                Thêm, sửa, xóa các môn thi trong kỳ thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('students.index', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user me-2"></i>
                                Quản lý thí sinh
                            </h5>
                            <p class="card-text text-muted">
                                Thêm, sửa, xóa các thí sinh trong kỳ thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-period-rooms.index', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-door-open me-2"></i>
                                Quản lý phòng thi
                            </h5>
                            <p class="card-text text-muted">
                                Thêm, sửa, xóa các phòng thi trong kỳ thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-period-proctors.index', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-tie me-2"></i>
                                Quản lý CBCT
                            </h5>
                            <p class="card-text text-muted">
                                Thêm, sửa, xóa cán bộ coi thi trong kỳ thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-periods.assignment.subjects', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-tasks me-2"></i>
                                Phân công môn thi - ca thi
                            </h5>
                            <p class="card-text text-muted">
                                Phân công môn thi được thi trong các ca thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-periods.assignment.rooms', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-door-open me-2"></i>
                                Phân công phòng thi
                            </h5>
                            <p class="card-text text-muted">
                                Phân công phòng thi và cán bộ coi thi cho các ca thi
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-periods.assignment.auto', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fa-solid fa-laptop-code"></i>
                                Tự động phân công
                            </h5>
                            <p class="card-text text-muted">
                                Tự động phân công
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-periods.results', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-poll me-2"></i>
                                Kết quả thi
                            </h5>
                            <p class="card-text text-muted">
                                Xem kết quả thi của thí sinh
                            </p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('exam-reports.index', $examPeriod) }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>
                                Báo cáo kết quả thi
                            </h5>
                            <p class="card-text text-muted">
                                Xem thống kê, báo cáo kết quả thi và xuất báo cáo theo môn học, phòng thi.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý nút xem thêm mô tả
    const showMoreBtn = document.querySelector('.show-more');
    if (showMoreBtn) {
        const descText = document.querySelector('.description-text');
        let isExpanded = false;

        showMoreBtn.addEventListener('click', function() {
            if (!isExpanded) {
                descText.style.display = 'block';
                descText.style.webkitLineClamp = 'unset';
                this.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Thu gọn';
            } else {
                descText.style.display = '-webkit-box';
                descText.style.webkitLineClamp = '2';
                this.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Xem thêm';
            }
            isExpanded = !isExpanded;
        });
    }

    // Xử lý nút xem thêm ca thi
    const collapseElement = document.getElementById('moreShifts');
    if (collapseElement) {
        collapseElement.addEventListener('show.bs.collapse', function() {
            const button = document.querySelector('[data-bs-target="#moreShifts"]');
            button.querySelector('.more-text').classList.add('d-none');
            button.querySelector('.less-text').classList.remove('d-none');
        });

        collapseElement.addEventListener('hide.bs.collapse', function() {
            const button = document.querySelector('[data-bs-target="#moreShifts"]');
            button.querySelector('.more-text').classList.remove('d-none');
            button.querySelector('.less-text').classList.add('d-none');
        });
    }
});
</script>

<style>
.btn-link {
    text-decoration: none;
    color: var(--bs-primary);
}

.btn-link:hover {
    color: var(--bs-primary-hover);
}

.description-text {
    transition: all 0.3s ease;
}
</style>
@endpush
@endsection 