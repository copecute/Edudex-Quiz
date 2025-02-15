@extends('layouts.app')

@section('title', 'Chỉnh sửa địa điểm thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chỉnh sửa địa điểm thi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('test_locations.update', $testLocation) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Tên cơ sở</label>
                            <input type="text" name="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $testLocation->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="address" 
                                   class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address', $testLocation->address) }}" required>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $testLocation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" 
                                       class="form-check-input @error('is_active') is-invalid @enderror"
                                       {{ old('is_active', $testLocation->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label">Kích hoạt địa điểm thi</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('test_locations.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 