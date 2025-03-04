@extends('layouts.app')

@section('title', 'Sửa thông tin thí sinh')

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
                    <li class="breadcrumb-item active">Sửa thông tin</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sửa thông tin thí sinh</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('students.update', [$examPeriod, $student]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- Cột trái -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số báo danh</label>
                                    <input type="text" class="form-control" value="{{ $student->exam_code }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Chọn môn thi</label>
                                    <select name="subject_id" class="form-select" required>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" 
                                                {{ $student->exam_period_subject_id == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mã sinh viên</label>
                                    <input type="text" name="student_code" class="form-control" 
                                           value="{{ $student->student_code }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Họ tên</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="{{ $student->full_name }}" required>
                                </div>
                            </div>
                            <!-- Cột phải -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" 
                                           value="{{ $student->phone }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea name="address" class="form-control" rows="3">{{ $student->address }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" name="birthday" class="form-control" 
                                           value="{{ optional($student->birthday)->format('Y-m-d') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Giới tính</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="1" {{ $student->gender ? 'selected' : '' }}>Nam</option>
                                        <option value="0" {{ !$student->gender ? 'selected' : '' }}>Nữ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('students.index', $examPeriod) }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 