@extends('layouts.app')

@section('title', 'Thêm kỳ thi mới')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Thêm mới</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thêm kỳ thi mới</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-periods.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên kỳ thi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Thời gian bắt đầu</label>
                                    <input type="date" class="form-control" id="start_time" name="start_time" 
                                           value="{{ old('start_time') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Thời gian kết thúc</label>
                                    <input type="date" class="form-control" id="end_time" name="end_time" 
                                           value="{{ old('end_time') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('exam-periods.index') }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Thêm mới</button>
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
    // Kiểm tra thời gian kết thúc phải sau thời gian bắt đầu
    $('#end_time').on('change', function() {
        var startTime = new Date($('#start_time').val());
        var endTime = new Date($(this).val());
        
        if (endTime <= startTime) {
            alert('Thời gian kết thúc phải sau thời gian bắt đầu');
            $(this).val('');
        }
    });
});
</script>
@endpush 