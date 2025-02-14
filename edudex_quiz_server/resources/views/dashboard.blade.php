@extends('layouts.app')

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
                            <span class="badge bg-primary">Quản trị viên</span>
                        @elseif(auth()->user()->role === 1)
                            <span class="badge bg-success">Giáo viên</span>
                        @else
                            <span class="badge bg-secondary">Nhân viên</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 0)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Quản lý tài khoản</h5>
                        <p class="card-text">Thêm, sửa, xóa và quản lý tài khoản người dùng</p>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">Truy cập</a>
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
                            @else Nhân viên (role = 2)
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