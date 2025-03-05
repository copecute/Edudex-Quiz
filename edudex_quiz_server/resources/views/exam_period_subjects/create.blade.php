@extends('layouts.app')

@section('title', 'Thêm môn thi mới')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-period-subjects.index', $examPeriod) }}">Môn thi - {{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item active">Thêm mới</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thêm môn thi mới</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-period-subjects.store', $examPeriod) }}" method="POST">
                        @csrf
                        
                        <input type="hidden" name="exam_period_id" value="{{ $examPeriod->id }}">

                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select class="form-select @error('subject_id') is-invalid @enderror" 
                                    id="subject_id" name="subject_id" required>
                                <option value="">Chọn môn học</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="exam_id" class="form-label">Đề thi <span class="text-danger">*</span></label>
                            <select class="form-select @error('exam_id') is-invalid @enderror" 
                                    id="exam_id" name="exam_id" required>
                                <option value="">Chọn đề thi</option>
                            </select>
                            @error('exam_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('exam-period-subjects.index', $examPeriod) }}" class="btn btn-secondary me-2">Hủy</a>
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
    // Cập nhật danh sách đề thi khi chọn môn học
    $('#subject_id').on('change', function() {
        const subjectId = $(this).val();
        const examSelect = $('#exam_id');
        
        examSelect.empty().append('<option value="">Chọn đề thi</option>');
        
        if (subjectId) {
            // Sử dụng route mới
            $.get(`{{ url('/exams/by-subject') }}/${subjectId}`, function(exams) {
                exams.forEach(exam => {
                    examSelect.append(`<option value="${exam.id}">${exam.name}</option>`);
                });
                
                // Khôi phục giá trị cũ nếu có
                const oldExamId = '{{ old('exam_id') }}';
                if (oldExamId) {
                    examSelect.val(oldExamId);
                }
            });
        }
    });

    // Trigger change event nếu có giá trị cũ
    if ($('#subject_id').val()) {
        $('#subject_id').trigger('change');
    }
});
</script>
@endpush 