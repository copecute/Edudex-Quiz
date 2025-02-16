@extends('layouts.app')

@section('title', 'Thêm ca thi mới - Edudex Quiz')

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
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Thêm ca thi mới</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0"><i class="fas fa-info-circle"></i> Kỳ thi: {{ $testSession->name }}</p>
                        <p class="mb-0"><i class="fas fa-clock"></i> Thời gian kỳ thi: {{ $testSession->start_time->format('d/m/Y H:i') }} - {{ $testSession->end_time->format('d/m/Y H:i') }}</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('test_sessions.test_shifts.store', $testSession) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Môn thi</label>
                            @if($testSessionSubjects->isEmpty())
                                <div class="alert alert-warning">
                                    Chưa có môn thi nào trong kỳ thi này. 
                                    <a href="{{ route('test_sessions.subjects.index', $testSession) }}">Thêm môn thi</a>
                                </div>
                            @else
                                <select name="test_session_subject_ids[]" 
                                        class="form-select select2-multiple @error('test_session_subject_ids') is-invalid @enderror" 
                                        multiple>
                                    @foreach($testSessionSubjects as $subject)
                                        <option value="{{ $subject->pivot->id }}" 
                                            {{ in_array($subject->pivot->id, old('test_session_subject_ids', [])) ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ $subject->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('test_session_subject_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên ca thi</label>
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thời gian bắt đầu</label>
                                <input type="datetime-local" name="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thời gian kết thúc</label>
                                <input type="datetime-local" name="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror"
                                       value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @error('time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" 
                                       class="form-check-input @error('is_active') is-invalid @enderror"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Kích hoạt ca thi</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                               class="btn btn-light me-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo mới
                            </button>
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
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Chọn môn thi',
        allowClear: true
    });
});
</script>
@endpush 