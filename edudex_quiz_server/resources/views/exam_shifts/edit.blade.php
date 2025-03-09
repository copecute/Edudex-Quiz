@extends('layouts.app')

@section('title', 'Chỉnh sửa ca thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-shifts.index', $examPeriod) }}">Ca thi - {{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chỉnh sửa ca thi</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-shifts.update', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="exam_period_id" value="{{ $examPeriod->id }}">

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên ca thi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $examShift->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $examShift->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ngày thi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('exam_date') is-invalid @enderror" 
                                   id="exam_date" name="exam_date" 
                                   value="{{ old('exam_date', $examShift->start_time->format('Y-m-d')) }}" required>
                            @error('exam_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Thời gian kỳ thi: {{ $examPeriod->start_time->format('d/m/Y') }} - {{ $examPeriod->end_time->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" 
                                           value="{{ old('start_time', $examShift->start_time->format('H:i')) }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" 
                                           value="{{ old('end_time', $examShift->end_time->format('H:i')) }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('exam-shifts.index', $examPeriod) }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
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
    // Kiểm tra giờ kết thúc phải sau giờ bắt đầu
    $('#end_time').on('change', function() {
        const startTime = $('#start_time').val();
        const endTime = $(this).val();
        
        if (startTime && endTime && startTime >= endTime) {
            alert('Giờ kết thúc phải sau giờ bắt đầu');
            $(this).val('');
        }
    });

    // Set min/max cho input ngày dựa vào thời gian kỳ thi
    const periodStart = '{{ $examPeriod->start_time->format("Y-m-d") }}';
    const periodEnd = '{{ $examPeriod->end_time->format("Y-m-d") }}';
    
    $('#exam_date').attr('min', periodStart);
    $('#exam_date').attr('max', periodEnd);
});
</script>
@endpush 