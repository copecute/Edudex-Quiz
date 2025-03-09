@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
    <div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
                <div class="card bg-primary text-white border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                            <div class="position-relative">
                        <img src="{{ Auth::user()->accountInfo->avatar 
                            ? asset('storage/avatars/' . Auth::user()->accountInfo->avatar) 
                            : asset('images/default-avatar.jpg') }}" 
                                    class="rounded-circle border border-3 border-white shadow" width="90" height="90">
                            </div>
                            <div class="ms-4">
                                <h3 class="mb-1 fw-bold">Xin chào, {{ Auth::user()->accountInfo->fullName }}!</h3>
                                <p class="mb-0 d-flex align-items-center opacity-75">
                                @switch(Auth::user()->role)
                                    @case(0)
                                            <span class="badge bg-white text-primary">Cán bộ coi thi</span>
                                        @break

                                    @case(1)
                                            <span class="badge bg-white text-primary">Giáo viên</span>
                                        @break

                                    @case(2)
                                            <span class="badge bg-white text-primary">Admin</span>
                                        @break
                                @endswitch
                                    <span class="ms-2">{{ Auth::user()->email }}</span>
                            </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="nav nav-pills flex-column" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="menu-link d-flex align-items-center p-3 active" id="v-pills-schedule-tab"
                                data-bs-toggle="pill" data-bs-target="#v-pills-schedule" type="button" role="tab"
                                aria-selected="true">
                                <div class="nav-icon me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="nav-text">
                                    <span class="d-block fw-medium">Lịch coi thi</span>
                                    <small class="text-muted">Xem lịch phân công coi thi</small>
    </div>
                            </button>

                            @if (Auth::user()->role == 2)
                                <button class="menu-link d-flex align-items-center p-3" id="v-pills-stats-tab"
                                    data-bs-toggle="pill" data-bs-target="#v-pills-stats" type="button" role="tab">
                                    <div class="nav-icon me-3">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="nav-t">
                                        <span class="d-block fw-medium">Thống kê</span>
                                        <small class="text-muted">Xem số liệu thống kê</small>
                                    </div>
                                </button>
                            @endif
                            <button class="menu-link d-flex align-items-center p-3" id="v-pills-management-tab"
                                data-bs-toggle="pill" data-bs-target="#v-pills-management" type="button" role="tab">
                                <div class="nav-icon me-3">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <div class="nav-t">
                                    @if (Auth::user()->role == 2)
                                        <span class="d-block fw-medium">Quản lý hệ thống</span>
                                        <small class="text-muted">Quản lý tài nguyên</small>
                                    @else
                                        <span class="d-block fw-medium">Chức năng</span>
                                        <small class="text-muted">Chức năng quản lý</small>
                                    @endif
                        </div>
                            </button>

                        </div>
                    </div>
                </div>

                @if (Auth::user()->role == 2)
                    <!-- Quick Stats -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted mb-3 fw-bold">
                                <i class="fas fa-chart-pie me-2"></i>Thống kê nhanh
                            </h6>
                            <div class="quick-stat mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="quick-stat-icon bg-primary bg-gradient rounded p-3">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="text-muted small">Tài khoản</div>
                                        <div class="h5 mb-0">{{ \App\Models\Account::count() }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="quick-stat mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="quick-stat-icon bg-success bg-gradient rounded p-3">
                                        <i class="fas fa-building text-white"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="text-muted small">Cơ sở</div>
                                        <div class="h5 mb-0">{{ \App\Models\Facility::count() }}</div>
                </div>
            </div>
        </div>
                            <div class="quick-stat">
                                <div class="d-flex align-items-center">
                                    <div class="quick-stat-icon bg-info bg-gradient rounded p-3">
                                        <i class="fas fa-calendar-check text-white"></i>
                        </div>
                        <div class="ms-3">
                                        <div class="text-muted small">Kỳ thi</div>
                                        <div class="h5 mb-0">{{ \App\Models\ExamPeriod::count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Schedule Tab -->
                    <div class="tab-pane fade show active" id="v-pills-schedule" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Lịch phân công coi thi</h5>
                            </div>
                            <div class="card-body bg-light">
                                @php
                                    $examPeriods = \App\Models\ExamPeriod::whereHas('proctors', function ($query) {
                                        $query->where('account_id', Auth::id());
                                    })
                                        ->where(function ($query) {
                                            $now = now();
                                            $query->where('is_active', true)->where('end_time', '>=', $now);
                                        })
                                        ->with([
                                            'examShifts' => function ($query) {
                                                $query
                                                    ->where('is_active', true)
                                                    ->whereHas('rooms.examShiftRooms', function ($q) {
                                                        $q->where('exam_period_proctor_id', function ($sq) {
                                                            $sq->select('id')
                                                                ->from('exam_period_proctors')
                                                                ->where('account_id', Auth::id())
                                                                ->limit(1);
                                                        });
                                                    })
                                                    ->orderBy('start_time');
                                            },
                                            'examShifts.rooms' => function ($query) {
                                                $query->whereHas('examShiftRooms', function ($q) {
                                                    $q->where('exam_period_proctor_id', function ($sq) {
                                                        $sq->select('id')
                                                            ->from('exam_period_proctors')
                                                            ->where('account_id', Auth::id())
                                                            ->limit(1);
                                                    });
                                                });
                                            },
                                            'examShifts.rooms.room.facility',
                                            'examShifts.rooms.examShiftRooms' => function ($query) {
                                                $query->where('exam_period_proctor_id', function ($sq) {
                                                    $sq->select('id')
                                                        ->from('exam_period_proctors')
                                                        ->where('account_id', Auth::id())
                                                        ->limit(1);
                                                });
                                            },
                                            'examShifts.rooms.examShiftRooms.examPeriodSubject.subject',
                                        ])
                                        ->orderBy('start_time')
                                        ->get();
                                @endphp

                                @forelse($examPeriods as $period)
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <h6 class="mb-0 flex-grow-1 fw-bold">{{ $period->name }}</h6>
                                            <span
                                                class="badge {{ $period->start_time->isFuture() ? 'bg-warning' : 'bg-success' }} bg-gradient">
                                                {{ $period->start_time->isFuture() ? 'Sắp diễn ra' : 'Đang diễn ra' }}
                                            </span>
                                        </div>

                                        @if ($period->examShifts->isEmpty())
                                            <div class="alert alert-warning bg-warning bg-opacity-10 border-warning">
                                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                                Bạn chưa được phân công phòng thi nào trong kỳ thi này.
                                            </div>
                                        @else
                                            <div class="row g-3">
                                                @foreach ($period->examShifts as $shift)
                                                    @if ($shift->rooms->isEmpty())
                                                        <div class="col-12">
                                                            <div
                                                                class="alert alert-warning bg-warning bg-opacity-10 border-warning">
                                                                <i
                                                                    class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                                                Bạn chưa được phân công phòng thi nào trong ca thi
                                                                {{ $shift->name }}.
                                                            </div>
                </div>
                                                    @else
                                                        @foreach ($shift->rooms as $room)
                                                            @php
                                                                $shiftRoom = $room->examShiftRooms->first();
                                                            @endphp
                                                            <div class="col-md-6 col-xl-4">
                                                                <div class="card h-100 border-0 shadow-sm hover-shadow">
                                                                    <div class="card-header bg-white border-0 py-3">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center">
                                                                            <span class="badge bg-primary bg-gradient">
                                                                                <i class="far fa-clock me-1"></i>
                                                                                {{ $shift->start_time->format('H:i') }} -
                                                                                {{ $shift->end_time->format('H:i') }}
                                                                            </span>
                                                                            <span class="badge bg-secondary bg-gradient">
                                                                                <i class="far fa-calendar-alt me-1"></i>
                                                                                {{ $shift->start_time->format('d/m/Y') }}
                                                                            </span>
            </div>
        </div>
                <div class="card-body">
                                                                        <h6 class="card-title text-primary mb-2 fw-bold">
                                                                            <i class="fas fa-door-open me-1"></i>
                                                                            {{ $room->room->name }}
                                                                        </h6>
                                                                        <p class="card-text small mb-1">
                                                                            <i class="fas fa-building me-1"></i>
                                                                            {{ $room->room->facility->name }}
                                                                        </p>
                                                                        @if ($shiftRoom && $shiftRoom->examPeriodSubject)
                                                                            <p class="card-text small mb-0 text-success">
                                                                                <i class="fas fa-book me-1"></i>
                                                                                {{ $shiftRoom->examPeriodSubject->subject->name }}
                                                                            </p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="alert alert-info bg-info bg-opacity-10 border-info">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        Bạn chưa được phân công coi thi cho kỳ thi nào.
                                    </div>
                                @endforelse
                        </div>
                        </div>
                    </div>

                    @if (Auth::user()->role == 2)
                        <!-- Stats Tab -->
                        <div class="tab-pane fade" id="v-pills-stats" role="tabpanel">
                            <div class="row g-4">
                                <!-- Thống kê tổng quan -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-chart-line text-primary me-2"></i>
                                                Thống kê tổng quan
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-4">
                                                <div class="col-md-3">
                                                    <div class="card bg-primary bg-gradient text-white">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <h6 class="text-white-50">Tài khoản</h6>
                                                                    <h3 class="mb-0">{{ \App\Models\Account::count() }}
                                                                    </h3>
                                                                </div>
                                                                <div class="fs-1 opacity-50">
                                                                    <i class="fas fa-users"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="card bg-success bg-gradient text-white">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <h6 class="text-white-50">Kỳ thi</h6>
                                                                    <h3 class="mb-0">
                                                                        {{ \App\Models\ExamPeriod::count() }}</h3>
                                                                </div>
                                                                <div class="fs-1 opacity-50">
                                                                    <i class="fas fa-calendar-check"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="card bg-info bg-gradient text-white">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <h6 class="text-white-50">Đề thi</h6>
                                                                    <h3 class="mb-0">{{ \App\Models\Exam::count() }}
                                                                    </h3>
                                                                </div>
                                                                <div class="fs-1 opacity-50">
                                                                    <i class="fas fa-file-alt"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="card bg-warning bg-gradient text-white">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <h6 class="text-white-50">Câu hỏi</h6>
                                                                    <h3 class="mb-0">{{ \App\Models\Question::count() }}
                                                                    </h3>
                                                                </div>
                                                                <div class="fs-1 opacity-50">
                                                                    <i class="fas fa-question-circle"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thống kê chi tiết -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                                Thống kê đào tạo
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-university me-2"></i>Khoa</span>
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ \App\Models\Faculty::count() }}</span>
                                                </div>
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-book me-2"></i>Ngành</span>
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ \App\Models\Major::count() }}</span>
                                                </div>
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-book-open me-2"></i>Môn học</span>
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ \App\Models\Subject::count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                Thống kê cơ sở vật chất
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-warehouse me-2"></i>Cơ sở</span>
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ \App\Models\Facility::count() }}</span>
                                                </div>
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-door-open me-2"></i>Phòng</span>
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ \App\Models\Room::count() }}</span>
                                                </div>
                                                <div
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fas fa-chair me-2"></i>Tổng số chỗ ngồi</span>
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ \App\Models\Room::sum('capacity') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thống kê kỳ thi đang diễn ra -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                                Kỳ thi đang diễn ra
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $activeExamPeriods = \App\Models\ExamPeriod::where('is_active', true)
                                                    ->where('start_time', '<=', now())
                                                    ->where('end_time', '>=', now())
                                                    ->withCount(['examPeriodSubjectStudents', 'proctors'])
                                                    ->get();
                                            @endphp

                                            @if ($activeExamPeriods->isEmpty())
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Không có kỳ thi nào đang diễn ra
                                                </div>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Tên kỳ thi</th>
                                                                <th>Thời gian</th>
                                                                <th>Thí sinh</th>
                                                                <th>CBCT</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($activeExamPeriods as $period)
                                                                <tr>
                                                                    <td>{{ $period->name }}</td>
                                                                    <td>
                                                                        <small>
                                                                            {{ $period->start_time->format('d/m/Y H:i') }}
                                                                            -
                                                                            {{ $period->end_time->format('d/m/Y H:i') }}
                                                                        </small>
                                                                    </td>
                                                                    <td>{{ $period->exam_period_subject_students_count }}
                                                                    </td>
                                                                    <td>{{ $period->proctors_count }}</td>
                                                                    <td>
                                                                        <a href="{{ route('exam-periods.dashboard', $period) }}"
                                                                            class="btn btn-sm btn-primary">
                                                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                </div>
            </div>
        </div>
    </div>
    @endif

                    <!-- Management Tab -->
                    <div class="tab-pane fade" id="v-pills-management" role="tabpanel">
    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-primary me-2"></i>
                        Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                                            @if (Auth::user()->role == 2)
                        <div class="col-md-3">
                                                    <a href="{{ route('accounts.create') }}"
                                                        class="text-decoration-none">
                                <div class="card action-card bg-light border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3">
                                            <i class="fas fa-user-plus fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="mb-2">Thêm tài khoản</h6>
                                                                <p class="text-muted small mb-0">Tạo tài khoản mới cho
                                                                    người dùng</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                                                    <a href="{{ route('facilities.create') }}"
                                                        class="text-decoration-none">
                                <div class="card action-card bg-light border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3">
                                            <i class="fas fa-building fa-2x text-success"></i>
                                        </div>
                                        <h6 class="mb-2">Thêm cơ sở thi</h6>
                                        <p class="text-muted small mb-0">Tạo cơ sở thi mới</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('rooms.create') }}" class="text-decoration-none">
                                <div class="card action-card bg-light border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3">
                                            <i class="fas fa-door-open fa-2x text-info"></i>
                                        </div>
                                        <h6 class="mb-2">Thêm phòng thi</h6>
                                        <p class="text-muted small mb-0">Tạo phòng thi mới</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif

                                            @if (Auth::user()->role == 1)
                                                <div class="col-md-3">
                                                    <a href="{{ route('questions.index') }}" class="text-decoration-none">
                                                        <div class="card action-card bg-light border-0 h-100">
                                                            <div class="card-body text-center p-4">
                                                                <div class="action-icon mb-3">
                                                                    <i class="fas fa-question-circle fa-2x text-primary"></i>
                                                                </div>
                                                                <h6 class="mb-2">Ngân hàng câu hỏi</h6>
                                                                <p class="text-muted small mb-0">Quản lý ngân hàng câu hỏi
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="col-md-3">
                                                    <a href="{{ route('exams.index') }}" class="text-decoration-none">
                                                        <div class="card action-card bg-light border-0 h-100">
                                                            <div class="card-body text-center p-4">
                                                                <div class="action-icon mb-3">
                                                                    <i class="fas fa-file-alt fa-2x text-primary"></i>
                                                                </div>
                                                                <h6 class="mb-2">Đề thi</h6>
                                                                <p class="text-muted small mb-0">Quản lý đề thi
                                                                </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endif

                        <div class="col-md-3">
                            <a href="{{ route('profile.show') }}" class="text-decoration-none">
                                <div class="card action-card bg-light border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3">
                                            <i class="fas fa-user-edit fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="mb-2">Thông tin cá nhân</h6>
                                                            <p class="text-muted small mb-0">Cập nhật thông tin của bạn
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <!-- Test Pages Section -->
                        <div class="col-12 mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-flask text-primary me-2"></i>
                                        Test Phân quyền
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h6 class="d-flex align-items-center text-danger">
                                                        <i class="fas fa-lock me-2"></i>
                                                        Admin Page
                                                    </h6>
                                                        <p class="small text-muted mb-3">Chỉ Admin
                                                            (role 2) mới có thể truy cập</p>
                                                        <a href="{{ route('test.admin') }}"
                                                            class="btn btn-outline-danger btn-sm">
                                                        Truy cập
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h6 class="d-flex align-items-center text-success">
                                                        <i class="fas fa-chalkboard-teacher me-2"></i>
                                                        Teacher Page
                                                    </h6>
                                                        <p class="small text-muted mb-3">Admin và
                                                            Teacher (role 2,1) có thể truy cập</p>
                                                        <a href="{{ route('test.teacher') }}"
                                                            class="btn btn-outline-success btn-sm">
                                                        Truy cập
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h6 class="d-flex align-items-center text-info">
                                                        <i class="fas fa-user me-2"></i>
                                                        Staff Page
                                                    </h6>
                                                        <p class="small text-muted mb-3">Tất cả người
                                                            dùng có thể truy cập</p>
                                                        <a href="{{ route('test.staff') }}"
                                                            class="btn btn-outline-info btn-sm">
                                                        Truy cập
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <div class="row g-3 mt-4">
                        <!-- Quản lý cơ sở vật chất -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-building text-primary me-2"></i>
                                        Cơ sở vật chất
                                    </h5>
                                    <p class="card-text">Quản lý thông tin cơ sở và phòng học.</p>
                                    <div class="list-group">
                                                <a href="{{ route('facilities.index') }}"
                                                    class="list-group-item list-group-item-action">
                                            <i class="fas fa-warehouse me-2"></i>Quản lý cơ sở
                                        </a>
                                                <a href="{{ route('rooms.index') }}"
                                                    class="list-group-item list-group-item-action">
                                            <i class="fas fa-door-open me-2"></i>Quản lý phòng
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quản lý đào tạo -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                                        Đào tạo
                                    </h5>
                                            <p class="card-text">Quản lý thông tin khoa, ngành, môn học và
                                                ngân hàng câu hỏi.</p>
                                    <div class="list-group">
                                                <a href="{{ route('faculties.index') }}"
                                                    class="list-group-item list-group-item-action">
                                            <i class="fas fa-university me-2"></i>Quản lý khoa
                                        </a>
                                                <a href="{{ route('majors.index') }}"
                                                    class="list-group-item list-group-item-action">
                                            <i class="fas fa-book me-2"></i>Quản lý ngành
                                        </a>
                                                <a href="{{ route('subjects.index') }}"
                                                    class="list-group-item list-group-item-action">
                                            <i class="fas fa-book-open me-2"></i>Quản lý môn học
                                        </a>
                                                <a href="{{ route('questions.index') }}"
                                                    class="list-group-item list-group-item-action">
                                                    <i class="fas fa-question-circle me-2"></i>Ngân hàng
                                                    câu hỏi
                                                </a>
                                                <a href="{{ route('exams.index') }}"
                                                    class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-alt me-2"></i>Danh sách đề thi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                                <!-- Tổ chức kỳ thi -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Tổ chức kỳ thi
                                            </h5>
                                            <p class="card-text">Quản lý thông tin kỳ thi, cán bộ coi thi
                                                và phòng thi.</p>
                                            <div class="list-group">
                                                <a href="{{ route('exam-periods.index') }}"
                                                    class="list-group-item list-group-item-action">
                                                    <i class="fas fa-calendar-alt me-2"></i>Danh sách kỳ
                                                    thi
                                                </a>
                                                <a href="{{ route('exam-periods.index') }}"
                                                    class="list-group-item list-group-item-action">
                                                    <i class="fas fa-file-alt me-2"></i> Trình hướng dẫn tổ
                                                    chức kỳ thi
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
        </div>
    </div>
</div>
@endsection 
