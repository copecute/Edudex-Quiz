@extends('layouts.app')

@section('title', 'Thêm khoa mới - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thêm khoa mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('faculties.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mã khoa</label>
                            <input type="text" name="code" value="{{ old('code') }}" 
                                   class="form-control @error('code') is-invalid @enderror" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên khoa</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" 
                                      rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('faculties.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Thêm mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 