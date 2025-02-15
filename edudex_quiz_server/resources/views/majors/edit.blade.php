@extends('layouts.app')

@section('title', 'Chỉnh sửa ngành - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chỉnh sửa ngành</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('majors.update', $major) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Mã ngành</label>
                            <input type="text" name="code" 
                                   value="{{ old('code', $major->code) }}" 
                                   class="form-control @error('code') is-invalid @enderror" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên ngành</label>
                            <input type="text" name="name" 
                                   value="{{ old('name', $major->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Khoa</label>
                            <select name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
                                <option value="">Chọn khoa</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}" 
                                        {{ old('faculty_id', $major->faculty_id) == $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('faculty_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" 
                                      rows="3">{{ old('description', $major->description) }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('majors.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 