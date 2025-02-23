@extends('layouts.app')

@section('title', 'Dashboard - Edudex Quiz')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-4">
        @if(auth()->user()->role === 0)
        <!-- Admin Menu -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-users"></i> Quản lý người dùng
                    </h5>
                    <p class="card-text">Quản lý tài khoản người dùng trong hệ thống</p>
                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-university"></i> Quản lý khoa
                    </h5>
                    <p class="card-text">Quản lý thông tin các khoa</p>
                    <a href="{{ route('faculties.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-graduation-cap"></i> Quản lý ngành
                    </h5>
                    <p class="card-text">Quản lý thông tin các ngành học</p>
                    <a href="{{ route('majors.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-user-graduate"></i> Quản lý thí sinh
                    </h5>
                    <p class="card-text">Quản lý thông tin thí sinh và ngành học</p>
                    <a href="{{ route('students.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-clipboard-list"></i> Quản lý kỳ thi
                    </h5>
                    <p class="card-text">Thêm, sửa, xóa và quản lý các kỳ thi</p>
                    <a href="{{ route('test_sessions.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Quản lý địa điểm thi</h5>
                    <p class="card-text">Quản lý các địa điểm tổ chức thi.</p>
                    <a href="{{ route('test_locations.index') }}" class="btn btn-primary">
                        <i class="fas fa-map-marker-alt"></i> Quản lý địa điểm
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Quản lý phòng thi</h5>
                    <p class="card-text">Quản lý các phòng thi tại các địa điểm.</p>
                    <a href="{{ route('test_rooms.index') }}" class="btn btn-primary">
                        <i class="fas fa-door-open"></i> Quản lý phòng thi
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role <= 1)
        <!-- Teacher Menu -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-book"></i> Quản lý môn học
                    </h5>
                    <p class="card-text">Quản lý môn học và tags trong hệ thống</p>
                    <a href="{{ route('subjects.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-question-circle"></i> Ngân hàng câu hỏi
                    </h5>
                    <p class="card-text">Quản lý câu hỏi trong ngân hàng đề thi</p>
                    <a href="{{ route('questions.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-file-alt"></i> Quản lý đề thi
                    </h5>
                    <p class="card-text">Tạo và quản lý các đề thi trong hệ thống</p>
                    <a href="{{ route('test_papers.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 2)
        <!-- Proctor Menu -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-clock"></i> Quản lý ca thi
                    </h5>
                    <p class="card-text">Quản lý và giám sát các ca thi</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Thử nghiệm phân quyền</h5>
                    <p class="card-text">Tài khoản: {{ auth()->user()->username }}, Vai trò: 
                            @if(auth()->user()->role === 0)Quản trị viên (role = 0)
                            @elseif(auth()->user()->role === 1)Giáo viên (role = 1)
                            @else Cán bộ coi thi (role = 2)
                            @endif
                        </p>
                    <a href="/test/admin" class="btn btn-primary">admin</a>
                    <a href="/test/teacher" class="btn btn-primary">teacher</a>
                    <a href="/test/staff" class="btn btn-primary">staff</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-file-alt"></i> Quản lý bài làm
                    </h5>
                    <p class="card-text">{{ \App\Models\TestSubmission::count() }} bài làm</p>
                    <a href="{{ route('test-submissions.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Truy cập
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection