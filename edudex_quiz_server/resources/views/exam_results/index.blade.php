@extends('layouts.app')

@section('title', 'Kết quả thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item active">Kết quả thi</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kết quả thi</h5>
                </div>
                <div class="card-body">
                    <!-- Form tìm kiếm -->
                    <form class="mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Tìm theo SBD, mã SV, họ tên..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Tìm kiếm
                                    </button>
                                    <a href="{{ route('exam-periods.results', $examPeriod) }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i> Đặt lại
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Chọn môn thi --</option>
                                    @foreach($examPeriod->examPeriodSubjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="shift_id" class="form-select">
                                    <option value="">-- Chọn ca thi --</option>
                                    @foreach($examPeriod->examShifts as $shift)
                                        <option value="{{ $shift->id }}"
                                            {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="room_id" class="form-select">
                                    <option value="">-- Chọn phòng thi --</option>
                                    @foreach($examPeriod->examPeriodRooms as $examPeriodRoom)
                                        <option value="{{ $examPeriodRoom->id }}"
                                            {{ request('room_id') == $examPeriodRoom->id ? 'selected' : '' }}>
                                            {{ $examPeriodRoom->room->name }} ({{ $examPeriodRoom->room->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Nút ẩn/hiện cột -->
                    <div class="mb-3 d-flex justify-content-between">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-columns"></i> Hiển thị cột
                            </button>
                            <div class="dropdown-menu p-2">
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="togglePeriodCode" data-column="period-code">
                                    <label class="form-check-label">Mã kỳ thi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="toggleShiftCode" data-column="shift-code">
                                    <label class="form-check-label">Mã ca thi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="toggleExamCode" data-column="exam-code">
                                    <label class="form-check-label">Mã đề thi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="toggleRoomCode" data-column="room-code">
                                    <label class="form-check-label">Mã phòng</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="toggleProctorCode" data-column="proctor-code">
                                    <label class="form-check-label">Mã CBCT</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="toggleNote" data-column="note">
                                    <label class="form-check-label">Ghi chú</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input toggle-column" type="checkbox" id="toggleReviewNote" data-column="review-note">
                                    <label class="form-check-label">Ghi chú phúc khảo</label>
                                </div>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Xuất kết quả
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('exam-periods.results.export', [
                                    'examPeriod' => $examPeriod,
                                    'format' => 'excel',
                                    'search' => request('search'),
                                    'subject_id' => request('subject_id'),
                                    'shift_id' => request('shift_id'),
                                    'room_id' => request('room_id')
                                ]) }}">
                                    <i class="fas fa-file-excel me-1"></i> Xuất Excel
                                </a>
                                <a class="dropdown-item" href="{{ route('exam-periods.results.export', [
                                    'examPeriod' => $examPeriod,
                                    'format' => 'word',
                                    'search' => request('search'),
                                    'subject_id' => request('subject_id'),
                                    'shift_id' => request('shift_id'),
                                    'room_id' => request('room_id')
                                ]) }}">
                                    <i class="fas fa-file-word me-1"></i> Xuất Word
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Thống kê -->
                    <div class="alert alert-info mb-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <strong>Thí sinh nộp bài:</strong> {{ $results->total() }}
                            </div>
                            <div class="col-md-3">
                                <strong>Điểm trung bình:</strong>
                                {{ number_format($stats['avg_score'], 2) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Điểm cao nhất:</strong>
                                {{ number_format($stats['max_score'], 2) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Điểm thấp nhất:</strong>
                                {{ number_format($stats['min_score'], 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Bảng kết quả -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="column-period-code">Mã kỳ thi</th>
                                    <th class="column-shift-code">Mã ca thi</th>
                                    <th>Mã môn thi</th>
                                    <th class="column-exam-code">Mã đề thi</th>
                                    <th class="column-room-code">Mã phòng</th>
                                    <th class="column-proctor-code">Mã CBCT</th>
                                    <th>SBD</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Số câu đúng/Tổng số</th>
                                    <th>Điểm</th>
                                    <th class="column-note">Ghi chú</th>
                                    <th class="column-review-note">Ghi chú phúc khảo</th>
                                    <th>Log</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td class="column-period-code">{{ $result->exam_period_code }}</td>
                                    <td class="column-shift-code">{{ $result->exam_shift_code }}</td>
                                    <td>{{ $result->exam_subject_code }}</td>
                                    <td class="column-exam-code">{{ $result->exam_code }}</td>
                                    <td class="column-room-code">{{ $result->room_code }}</td>
                                    <td class="column-proctor-code">{{ $result->proctor_code }}</td>
                                    <td>{{ $result->student->exam_code }}</td>
                                    <td>{{ $result->student->student_code }}</td>
                                    <td>{{ $result->student->full_name }}</td>
                                    <td>
                                        {{ $result->correct_answers }}/{{ $result->total_questions }}
                                        @if($result->correct_answers_after_review)
                                            <span class="text-muted">|</span>
                                            <span class="text-primary">{{ $result->correct_answers_after_review }}/{{ $result->total_questions }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ number_format($result->score, 2) }}
                                        @if($result->score_after_review)
                                            <span class="text-muted">|</span>
                                            <span class="text-primary">{{ number_format($result->score_after_review, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="column-note">{{ $result->note }}</td>
                                    <td class="column-review-note">{{ $result->review_note }}</td>
                                    <td>
                                        <a href="data:application/octet-stream;base64,{{ $result->log_file }}"
                                           download="{{ $result->student->exam_code }}_{{ $result->student->student_code }}.edudex"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-file-export"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('exam-periods.results.export', [
                                                    'examPeriod' => $examPeriod,
                                                    'format' => 'excel',
                                                    'student_id' => $result->exam_period_subject_student_id
                                                ]) }}">
                                                    <i class="fas fa-file-excel me-1"></i> Excel
                                                </a>
                                                <a class="dropdown-item" href="{{ route('exam-periods.results.export', [
                                                    'examPeriod' => $examPeriod,
                                                    'format' => 'word',
                                                    'student_id' => $result->exam_period_subject_student_id
                                                ]) }}">
                                                    <i class="fas fa-file-word me-1"></i> Word
                                                </a>
                                            </div>
                                        </div>
                                        <button type="button" 
                                            class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#reviewModal{{ $result->id }}">
                                            <i class="fas fa-edit"></i> Phúc khảo
                                        </button>
                                    </td>
                                </tr>
                                <!-- Modal Phúc khảo -->
                                <div class="modal fade" id="reviewModal{{ $result->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('exam-periods.results.review', [$examPeriod, $result]) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Phúc khảo bài thi</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Thí sinh</label>
                                                        <input type="text" class="form-control" readonly 
                                                            value="{{ $result->student->full_name }} ({{ $result->student->exam_code }})">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Số câu đúng sau phúc khảo</label>
                                                        <input type="number" name="correct_answers_after_review" 
                                                            class="form-control" required min="0"
                                                            id="correctAnswers{{ $result->id }}"
                                                            value="{{ $result->correct_answers_after_review ?? $result->correct_answers }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label mb-0">Điểm số sau phúc khảo</label>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    id="autoCalculate{{ $result->id }}" checked>
                                                                <label class="form-check-label">Tự động tính điểm</label>
                                                            </div>
                                                        </div>
                                                        <input type="number" name="score_after_review" 
                                                            class="form-control" required min="0" max="10" step="0.01"
                                                            id="scoreAfterReview{{ $result->id }}"
                                                            value="{{ $result->score_after_review ?? $result->score }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Ghi chú phúc khảo</label>
                                                        <textarea name="review_note" class="form-control" required
                                                            rows="3">{{ $result->review_note }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $results->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm xử lý ẩn/hiện cột
        function toggleColumn(checkbox) {
            const columnClass = 'column-' + this.dataset.column;
            const cells = document.querySelectorAll('.' + columnClass);
            cells.forEach(cell => {
                cell.style.display = this.checked ? '' : 'none';
            });
        }
        
        // Gắn sự kiện và kích hoạt ngay cho mỗi checkbox
        document.querySelectorAll('.toggle-column').forEach(checkbox => {
            checkbox.addEventListener('change', toggleColumn);
            // Kích hoạt sự kiện change ngay khi tải trang
            checkbox.dispatchEvent(new Event('change'));
        });

        @foreach($results as $result)
            // Lấy các elements cho mỗi kết quả
            const correctAnswersInput{{ $result->id }} = document.getElementById('correctAnswers{{ $result->id }}');
            const scoreInput{{ $result->id }} = document.getElementById('scoreAfterReview{{ $result->id }}');
            const autoCalculateCheckbox{{ $result->id }} = document.getElementById('autoCalculate{{ $result->id }}');
            
            // Hàm tính điểm tự động
            function calculateScore{{ $result->id }}() {
                if (autoCalculateCheckbox{{ $result->id }}.checked) {
                    const correctAnswers = parseInt(correctAnswersInput{{ $result->id }}.value) || 0;
                    const totalQuestions = {{ $result->total_questions }};
                    const score = (correctAnswers / totalQuestions) * 10;
                    scoreInput{{ $result->id }}.value = score.toFixed(2);
                    scoreInput{{ $result->id }}.readOnly = true;
                } else {
                    scoreInput{{ $result->id }}.readOnly = false;
                }
            }
            
            // Gắn các event listeners
            correctAnswersInput{{ $result->id }}.addEventListener('input', calculateScore{{ $result->id }});
            autoCalculateCheckbox{{ $result->id }}.addEventListener('change', calculateScore{{ $result->id }});
            
            // Tính điểm ban đầu
            calculateScore{{ $result->id }}();
        @endforeach
    });
</script>
@endpush

@push('styles')
<style>
    /* Ẩn mặc định các cột */
    [class*="column-"] {
        display: none;
    }
    
    /* Hiển thị cột mã đề thi mặc định */
    .column-exam-code {
        display: table-cell;
    }
</style>
@endpush 