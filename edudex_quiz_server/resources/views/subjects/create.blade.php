@extends('layouts.app')

@section('title', 'Thêm môn học mới')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Quản lý môn học</a></li>
                    <li class="breadcrumb-item active">Thêm mới</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        Thêm môn học mới
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('subjects.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">Mã môn học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code') }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên môn học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="credits" class="form-label">Số tín chỉ <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('credits') is-invalid @enderror" 
                                   id="credits" name="credits" value="{{ old('credits') }}" min="1" required>
                            @error('credits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="major_id" class="form-label">Ngành <span class="text-danger">*</span></label>
                            <select class="form-select @error('major_id') is-invalid @enderror" 
                                    id="major_id" name="major_id" required>
                                <option value="">Chọn ngành</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major->id }}" 
                                        {{ old('major_id') == $major->id ? 'selected' : '' }}>
                                        {{ $major->name }} ({{ $major->faculty->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('major_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 