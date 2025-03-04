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
                        <span class="badge {{ $examPeriod->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $examPeriod->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                        </span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Thời gian bắt đầu: {{ $examPeriod->start_time->format('H:i d/m/Y') }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Thời gian kết thúc: {{ $examPeriod->end_time->format('H:i d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-info-circle me-2"></i>
                                Mô tả: {{ $examPeriod->description ?? 'Không có mô tả' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Ca thi</h6>
                            <h2 class="mb-0">{{ $stats['total_shifts'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Môn thi</h6>
                            <h2 class="mb-0">{{ $stats['total_subjects'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Phòng thi</h6>
                            <h2 class="mb-0">{{ $stats['total_rooms'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="card-header">
                    <h5 class="mb-0">Danh sách ca thi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ca thi</th>
                                    <th>Thời gian</th>
                                    <th>Số môn thi</th>
                                    <th>Số phòng thi</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($examPeriod->examShifts as $shift)
                                <tr>
                                    <td>{{ $shift->name }}</td>
                                    <td>{{ $shift->start_time->format('H:i d/m/Y') }}</td>
                                    <td>{{ $shift->subjects_count }}</td>
                                    <td>{{ $shift->rooms_count }}</td>
                                    <td>
                                        <span class="badge {{ $shift->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $shift->is_active ? 'Hoạt động' : 'Đã khóa' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Các nút thao tác nhanh -->
            <div class="row g-4">
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
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
            </div>
        </div>
    </div>
</div>
@endsection 