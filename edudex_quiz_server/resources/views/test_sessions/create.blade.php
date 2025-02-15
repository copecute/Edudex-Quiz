@extends('layouts.app')

@section('title', 'Tạo kỳ thi mới - Edudex Quiz')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tạo kỳ thi mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('test_sessions.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tên kỳ thi</label>
                            <input type="text" name="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian bắt đầu</label>
                                    <input type="text" name="start_time" 
                                           class="form-control flatpickr @error('start_time') is-invalid @enderror"
                                           value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian kết thúc</label>
                                    <input type="text" name="end_time" 
                                           class="form-control flatpickr @error('end_time') is-invalid @enderror"
                                           value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" 
                                       class="form-check-input @error('is_active') is-invalid @enderror"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Kích hoạt kỳ thi</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('test_sessions.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Tạo mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
<script>
flatpickr(".flatpickr", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    locale: "vn",
    minDate: "today"
});
</script>
@endpush
@endsection 