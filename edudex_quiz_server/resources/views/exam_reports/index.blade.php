@extends('layouts.app')

@section('title', 'Báo cáo kết quả thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item active">Báo cáo kết quả thi</li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tổng quan kết quả thi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                                    <p class="text-muted mb-0">Tổng số thí sinh</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['total_subjects'] }}</h3>
                                    <p class="text-muted mb-0">Môn thi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $stats['total_results'] }}</h3>
                                    <p class="text-muted mb-0">Bài thi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ number_format($stats['avg_score'] ?? 0, 2) }}</h3>
                                    <p class="text-muted mb-0">Điểm trung bình</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ number_format($stats['max_score'] ?? 0, 2) }}</h3>
                                    <p class="text-muted mb-0">Điểm cao nhất</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ number_format($stats['min_score'] ?? 0, 2) }}</h3>
                                    <p class="text-muted mb-0">Điểm thấp nhất</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Báo cáo theo môn thi</h5>
                        </div>
                        <div class="card-body">
                            <p>Xem thống kê kết quả thi theo từng môn học, bao gồm:</p>
                            <ul>
                                <li>Điểm trung bình, cao nhất, thấp nhất</li>
                                <li>Tỷ lệ hoàn thành</li>
                                <li>Danh sách và xếp hạng thí sinh</li>
                            </ul>
                            <div class="text-center mt-4">
                                <a href="{{ route('exam-reports.by-subject', $examPeriod) }}" class="btn btn-primary">
                                    <i class="fas fa-book me-2"></i>Xem báo cáo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Báo cáo theo phòng thi</h5>
                        </div>
                        <div class="card-body">
                            <p>Xem thống kê kết quả thi theo từng phòng thi, bao gồm:</p>
                            <ul>
                                <li>Tổng số thí sinh trong phòng</li>
                                <li>Điểm trung bình mỗi phòng</li>
                                <li>Danh sách thí sinh và kết quả</li>
                            </ul>
                            <div class="text-center mt-4">
                                <a href="{{ route('exam-reports.by-room', $examPeriod) }}" class="btn btn-primary">
                                    <i class="fas fa-door-open me-2"></i>Xem báo cáo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Xếp hạng thí sinh</h5>
                        </div>
                        <div class="card-body">
                            <p>Xem bảng xếp hạng thí sinh theo điểm thi:</p>
                            <ul>
                                <li>Xếp hạng toàn kỳ thi</li>
                                <li>Xếp hạng theo từng môn thi</li>
                                <li>Thống kê điểm cao nhất</li>
                            </ul>
                            <div class="text-center mt-4">
                                <a href="{{ route('exam-reports.ranking', $examPeriod) }}" class="btn btn-primary">
                                    <i class="fas fa-trophy me-2"></i>Xem xếp hạng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Xuất báo cáo</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('exam-reports.export', [$examPeriod, 'excel']) }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Loại báo cáo</label>
                                    <select name="report_type" class="form-select" id="reportType">
                                        <option value="all">Tất cả kết quả thi</option>
                                        <option value="subject">Theo môn thi</option>
                                        <option value="room">Theo phòng thi</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 subject-filter d-none">
                                <div class="mb-3">
                                    <label class="form-label">Môn thi</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">Chọn môn thi</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">
                                                {{ $subject->subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 room-filter d-none">
                                <div class="mb-3">
                                    <label class="form-label">Phòng thi</label>
                                    <select name="room_id" class="form-select">
                                        <option value="">Chọn phòng thi</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">
                                                {{ $room->room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Định dạng</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success flex-grow-1">
                                            <i class="fas fa-file-excel me-2"></i>Excel
                                        </button>
                                        <button type="submit" class="btn btn-danger flex-grow-1" formaction="{{ route('exam-reports.export', [$examPeriod, 'word']) }}">
                                            <i class="fas fa-file-word me-2"></i>Word
                                        </button>
                                    </div>
                                </div>
                            </div>
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
    // hiện/ẩn filter theo loại báo cáo
    $('#reportType').on('change', function() {
        const reportType = $(this).val();
        
        // ẩn tất cả filter
        $('.subject-filter, .room-filter').addClass('d-none');
        
        // hiển thị filter tương ứng
        if (reportType === 'subject') {
            $('.subject-filter').removeClass('d-none');
        } else if (reportType === 'room') {
            $('.room-filter').removeClass('d-none');
        }
    });
});
</script>
@endpush 