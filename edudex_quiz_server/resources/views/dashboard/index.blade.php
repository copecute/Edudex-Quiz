@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white welcome-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <img src="{{ Auth::user()->accountInfo->avatar 
                            ? asset('storage/avatars/' . Auth::user()->accountInfo->avatar) 
                            : asset('images/default-avatar.jpg') }}" 
                             class="rounded-circle me-4" width="80" height="80">
                        <div>
                            <h3 class="mb-1">Xin chào, {{ Auth::user()->accountInfo->fullName }}!</h3>
                            <p class="mb-0 d-flex align-items-center">
                                @switch(Auth::user()->role)
                                    @case(0)
                                        <span class="badge bg-light text-primary">Cán bộ coi thi</span>
                                        @break
                                    @case(1)
                                        <span class="badge bg-light text-primary">Giáo viên</span>
                                        @break
                                    @case(2)
                                        <span class="badge bg-light text-primary">Admin</span>
                                        @break
                                @endswitch
                                <span class="ms-2 text-light">{{ Auth::user()->email }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @if(Auth::user()->role == 2)
    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 icon-bg-primary rounded-3 p-3">
                            <i class="fas fa-users text-white fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Tổng số tài khoản</h6>
                            <h3 class="mb-0">{{ \App\Models\Account::count() }}</h3>
                        </div>
                    </div>
                    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 icon-bg-success rounded-3 p-3">
                            <i class="fas fa-building text-white fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Cơ sở thi</h6>
                            <h3 class="mb-0">{{ \App\Models\Facility::count() }}</h3>
                        </div>
                    </div>
                    <a href="{{ route('facilities.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 icon-bg-info rounded-3 p-3">
                            <i class="fas fa-door-open text-white fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Phòng thi</h6>
                            <h3 class="mb-0">{{ \App\Models\Room::count() }}</h3>
                        </div>
                    </div>
                    <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                        @if(Auth::user()->role == 2)
                        <div class="col-md-3">
                            <a href="{{ route('accounts.create') }}" class="text-decoration-none">
                                <div class="card action-card bg-light border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3">
                                            <i class="fas fa-user-plus fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="mb-2">Thêm tài khoản</h6>
                                        <p class="text-muted small mb-0">Tạo tài khoản mới cho người dùng</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('facilities.create') }}" class="text-decoration-none">
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

                        <div class="col-md-3">
                            <a href="{{ route('profile.show') }}" class="text-decoration-none">
                                <div class="card action-card bg-light border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="action-icon mb-3">
                                            <i class="fas fa-user-edit fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="mb-2">Thông tin cá nhân</h6>
                                        <p class="text-muted small mb-0">Cập nhật thông tin của bạn</p>
                                    </div>
                                </div>
                            </a>
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
                                                    <p class="small text-muted mb-3">Chỉ Admin (role 2) mới có thể truy cập</p>
                                                    <a href="{{ route('test.admin') }}" class="btn btn-outline-danger btn-sm">
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
                                                    <p class="small text-muted mb-3">Admin và Teacher (role 2,1) có thể truy cập</p>
                                                    <a href="{{ route('test.teacher') }}" class="btn btn-outline-success btn-sm">
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
                                                    <p class="small text-muted mb-3">Tất cả người dùng có thể truy cập</p>
                                                    <a href="{{ route('test.staff') }}" class="btn btn-outline-info btn-sm">
                                                        Truy cập
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                        <a href="{{ route('facilities.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-warehouse me-2"></i>Quản lý cơ sở
                                        </a>
                                        <a href="{{ route('rooms.index') }}" class="list-group-item list-group-item-action">
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
                                    <p class="card-text">Quản lý thông tin khoa, ngành, môn học và ngân hàng câu hỏi.</p>
                                    <div class="list-group">
                                        <a href="{{ route('faculties.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-university me-2"></i>Quản lý khoa
                                        </a>
                                        <a href="{{ route('majors.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-book me-2"></i>Quản lý ngành
                                        </a>
                                        <a href="{{ route('subjects.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-book-open me-2"></i>Quản lý môn học
                                        </a>
                                        <a href="{{ route('questions.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-question-circle me-2"></i>Ngân hàng câu hỏi
                                        </a>
                                        <a href="{{ route('exams.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-alt me-2"></i>Danh sách đề thi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->role === 2)
                        <!-- Quản lý kỳ thi -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Kỳ thi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ App\Models\ExamPeriod::count() }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ route('exam-periods.index') }}" class="text-decoration-none">
                                                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quản lý ca thi -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Ca thi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ App\Models\ExamShift::count() }}</div>
                                        </div>
                                        <div class="col-auto">
                                            @if(isset($examPeriod))
                                                <a href="{{ route('exam-shifts.index', ['examPeriod' => $examPeriod->id]) }}" class="text-decoration-none">
                                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 