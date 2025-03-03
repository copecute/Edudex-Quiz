@extends('layouts.app')

@section('title', 'Thêm ca thi mới')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-shifts.index', $examPeriod) }}">Ca thi - {{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item active">Thêm mới</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thêm ca thi mới</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-shifts.store', $examPeriod) }}" method="POST">
                        @csrf

                        <input type="hidden" name="exam_period_id" value="{{ $examPeriod->id }}">

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên ca thi <span class="text-danger">*</span></label>
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
                                    <label for="start_time" class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Thời gian kỳ thi: {{ $examPeriod->start_time->format('d/m/Y H:i') }} - {{ $examPeriod->end_time->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('exam-shifts.index', $examPeriod) }}" class="btn btn-secondary me-2">Hủy</a>
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
    $('#start_time, #end_time').on('change', function() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (startTime && endTime && startTime >= endTime) {
            alert('Thời gian kết thúc phải sau thời gian bắt đầu');
            $('#end_time').val('');
        }
    });

    // Set min/max cho input thời gian dựa vào thời gian kỳ thi
    const periodStart = '{{ $examPeriod->start_time->format("Y-m-d\TH:i") }}';
    const periodEnd = '{{ $examPeriod->end_time->format("Y-m-d\TH:i") }}';
    
    $('#start_time').attr('min', periodStart);
    $('#start_time').attr('max', periodEnd);
    $('#end_time').attr('min', periodStart);
    $('#end_time').attr('max', periodEnd);
});
</script>
@endpush 