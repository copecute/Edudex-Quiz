@extends('layouts.app')

@section('title', 'Thêm thí sinh mới')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('students.index', $examPeriod) }}">Quản lý thí sinh</a></li>
                    <li class="breadcrumb-item active">Thêm mới</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thêm thí sinh mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('students.store', $examPeriod) }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Cột trái -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chọn môn thi</label>
                                    <select name="subject_ids[]" class="form-select" multiple required>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">
                                                {{ $subject->subject->name }}
                                                ({{ $subject->students->count() }} thí sinh)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Giữ Ctrl để chọn nhiều môn thi</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mã sinh viên</label>
                                    <input type="text" name="student_code" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Họ tên</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>
                            </div>
                            <!-- Cột phải -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea name="address" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" name="birthday" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Giới tính</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="1">Nam</option>
                                        <option value="0">Nữ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('students.index', $examPeriod) }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Thêm mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 