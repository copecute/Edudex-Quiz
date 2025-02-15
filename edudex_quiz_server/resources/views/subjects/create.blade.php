@extends('layouts.app')

@section('title', 'Thêm môn học mới - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thêm môn học mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('subjects.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mã môn học</label>
                            <input type="text" name="code" value="{{ old('code') }}" 
                                   class="form-control @error('code') is-invalid @enderror" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên môn học</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số tín chỉ</label>
                            <input type="number" name="credits" value="{{ old('credits', 3) }}" 
                                   class="form-control @error('credits') is-invalid @enderror" 
                                   min="1" max="10" required>
                            @error('credits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Khoa</label>
                            <select class="form-select" id="faculty-select">
                                <option value="">Chọn khoa</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ngành</label>
                            <select name="major_id" class="form-select @error('major_id') is-invalid @enderror" 
                                    id="major-select" required>
                                <option value="">Chọn ngành</option>
                                @foreach($faculties as $faculty)
                                    @foreach($faculty->majors as $major)
                                        <option value="{{ $major->id }}" 
                                            data-faculty="{{ $faculty->id }}"
                                            {{ old('major_id') == $major->id ? 'selected' : '' }}>
                                            {{ $major->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('major_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" 
                                      rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('subjects.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Thêm mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const facultySelect = document.getElementById('faculty-select');
    const majorSelect = document.getElementById('major-select');
    const majorOptions = Array.from(majorSelect.options);

    facultySelect.addEventListener('change', function() {
        const selectedFacultyId = this.value;
        
        // Reset major select
        majorSelect.value = '';
        
        // Show/hide major options based on selected faculty
        majorOptions.forEach(option => {
            if (!selectedFacultyId || option.dataset.faculty === selectedFacultyId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Trigger change event on page load if faculty is selected
    if (facultySelect.value) {
        facultySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection 