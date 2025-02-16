@extends('layouts.app')

@section('title', 'Trang quản trị - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Xin chào, {{ auth()->user()->username }}</h5>
                    <p class="card-text text-muted">
                        Vai trò: 
                        @if(auth()->user()->role === 0)
                            <span class="badge bg-danger">Quản trị viên</span>
                        @elseif(auth()->user()->role === 1)
                            <span class="badge bg-success">Giáo viên</span>
                        @else
                            <span class="badge bg-secondary">Cán bộ coi thi</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 0)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-users"></i> Quản lý tài khoản
                        </h5>
                        <p class="card-text">Thêm, sửa, xóa và quản lý tài khoản người dùng</p>
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
                        <p class="card-text">Thêm, sửa, xóa và quản lý khoa</p>
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
                        <p class="card-text">Thêm, sửa, xóa và quản lý ngành học</p>
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
                            <i class="fas fa-book"></i> Quản lý môn học
                        </h5>
                        <p class="card-text">Thêm, sửa, xóa và quản lý môn học</p>
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
                            <i class="fas fa-question-circle"></i> Quản lý câu hỏi
                        </h5>
                        <p class="card-text">Thêm, sửa, xóa và quản lý ngân hàng câu hỏi</p>
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
                        <p class="card-text">Tạo và quản lý các đề thi với phân bố câu hỏi theo tags</p>
                        <a href="{{ route('test_papers.index') }}" class="btn btn-primary">
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
    </div>
</div>
@endsection