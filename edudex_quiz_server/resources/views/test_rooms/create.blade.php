@extends('layouts.app')

@section('title', 'Thêm phòng thi mới - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thêm phòng thi mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('test_rooms.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Địa điểm</label>
                            <select name="test_location_id" 
                                    class="form-select @error('test_location_id') is-invalid @enderror" 
                                    required>
                                <option value="">Chọn địa điểm</option>
                                @foreach($testLocations as $location)
                                    <option value="{{ $location->id }}" 
                                        {{ old('test_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->address }})
                                    </option>
                                @endforeach
                            </select>
                            @error('test_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mã phòng</label>
                            <input type="text" name="code" 
                                   class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên phòng</label>
                            <input type="text" name="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sức chứa (số thí sinh)</label>
                            <input type="number" name="capacity" 
                                   class="form-control @error('capacity') is-invalid @enderror"
                                   value="{{ old('capacity', 30) }}" min="1" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" 
                                       class="form-check-input @error('is_active') is-invalid @enderror"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Kích hoạt phòng thi</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('test_rooms.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Tạo mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 