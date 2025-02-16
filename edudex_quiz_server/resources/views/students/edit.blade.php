@extends('layouts.app')

@section('title', 'Sửa thông tin thí sinh - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sửa thông tin thí sinh</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('students.update', $student) }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mã sinh viên</label>
                                <input type="text" name="code" 
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code', $student->code) }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $student->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" 
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $student->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" 
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $student->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" 
                                          class="form-control @error('address') is-invalid @enderror"
                                          rows="2">{{ old('address', $student->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" name="birthday" 
                                       class="form-control @error('birthday') is-invalid @enderror"
                                       value="{{ old('birthday', $student->birthday?->format('Y-m-d')) }}">
                                @error('birthday')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Giới tính</label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="1" {{ old('gender', $student->gender) == 1 ? 'selected' : '' }}>Nam</option>
                                    <option value="0" {{ old('gender', $student->gender) == 0 ? 'selected' : '' }}>Nữ</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Ngành học</label>
                                <select name="majors[]" class="form-select select2-majors @error('majors') is-invalid @enderror" 
                                        multiple required>
                                    @foreach($majors as $major)
                                        <option value="{{ $major->id }}" 
                                            {{ in_array($major->id, old('majors', $student->majors->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $major->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('majors')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Ngành chính</label>
                                <select name="main_major" class="form-select select2-main-major @error('main_major') is-invalid @enderror" 
                                        required>
                                    <option value="">Chọn ngành chính</option>
                                    @foreach($student->majors as $major)
                                        <option value="{{ $major->id }}" 
                                            {{ old('main_major', $major->pivot->is_main ? $major->id : '') == $major->id ? 'selected' : '' }}>
                                            {{ $major->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('main_major')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $student->status) == 1 ? 'selected' : '' }}>Đang học</option>
                                    <option value="0" {{ old('status', $student->status) == 0 ? 'selected' : '' }}>Đã nghỉ</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" name="avatar" 
                                       class="form-control @error('avatar') is-invalid @enderror"
                                       accept="image/*">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($student->avatar)
                                    <div class="mt-2">
                                        <img src="{{ $student->avatar_url }}" 
                                             alt="Avatar" 
                                             class="rounded-circle"
                                             width="100">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <a href="{{ route('students.index') }}" class="btn btn-light me-2">Hủy</a>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Khởi tạo Select2 cho ngành học
    $('.select2-majors').select2({
        theme: 'bootstrap-5',
        placeholder: 'Chọn ngành học',
        allowClear: true
    });

    // Khởi tạo Select2 cho ngành chính
    $('.select2-main-major').select2({
        theme: 'bootstrap-5',
        placeholder: 'Chọn ngành chính'
    });

    // Cập nhật danh sách ngành chính khi thay đổi ngành học
    $('.select2-majors').on('change', function() {
        const selectedMajors = $(this).val();
        const mainMajorSelect = $('.select2-main-major');
        const currentMainMajor = mainMajorSelect.val();
        
        // Xóa tất cả option cũ
        mainMajorSelect.empty().append('<option value="">Chọn ngành chính</option>');
        
        // Thêm các option mới từ ngành đã chọn
        selectedMajors.forEach(function(majorId) {
            const majorOption = $(`.select2-majors option[value="${majorId}"]`);
            mainMajorSelect.append(new Option(majorOption.text(), majorId, false, majorId == currentMainMajor));
        });
        
        mainMajorSelect.trigger('change');
    });
});
</script>
@endpush 