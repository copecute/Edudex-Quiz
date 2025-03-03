@extends('layouts.app')

@section('title', 'Chỉnh sửa thí sinh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-subjects.index', $examPeriod) }}">
                            Môn thi - {{ $examPeriod->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-subject-students.index', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}">
                            Thí sinh - {{ $examPeriodSubject->subject->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chỉnh sửa thí sinh</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-period-subject-students.update', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id, 'student' => $student->id]) }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_code" class="form-label">Mã sinh viên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('student_code') is-invalid @enderror" 
                                           id="student_code" name="student_code" value="{{ old('student_code', $student->student_code) }}" required>
                                    @error('student_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                           id="full_name" name="full_name" value="{{ old('full_name', $student->full_name) }}" required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="birthday" class="form-label">Ngày sinh</label>
                                    <input type="date" class="form-control @error('birthday') is-invalid @enderror" 
                                           id="birthday" name="birthday" value="{{ old('birthday', optional($student->birthday)->format('Y-m-d')) }}">
                                    @error('birthday')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                                    <select class="form-select @error('gender') is-invalid @enderror" 
                                            id="gender" name="gender" required>
                                        <option value="">Chọn giới tính</option>
                                        <option value="1" {{ old('gender', $student->gender) ? 'selected' : '' }}>Nam</option>
                                        <option value="0" {{ old('gender', $student->gender) === 0 ? 'selected' : '' }}>Nữ</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $student->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Ảnh đại diện</label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                           id="avatar" name="avatar">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($student->avatar)
                                        <div class="mt-2" id="current-avatar">
                                            <img src="{{ asset($student->avatar) }}" alt="Avatar" class="img-thumbnail" style="max-height: 100px">
                                        </div>
                                    @endif
                                    <div id="preview" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $student->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('exam-period-subject-students.index', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                               class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Preview ảnh trước khi upload
    $('#avatar').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').html(`
                    <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px">
                `);
                $('#current-avatar').hide();
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush 