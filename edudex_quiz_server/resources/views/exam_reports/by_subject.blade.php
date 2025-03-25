@extends('layouts.app')

@section('title', 'Báo cáo theo môn thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-reports.index', $examPeriod) }}">Báo cáo kết quả thi</a></li>
                    <li class="breadcrumb-item active">Báo cáo theo môn thi</li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thống kê theo môn thi</h5>
                    <a href="{{ route('exam-reports.export', [$examPeriod, 'excel']) }}?report_type=all" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Xuất Excel
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Môn thi</th>
                                    <th>Mã môn</th>
                                    <th>Số thí sinh</th>
                                    <th>Hoàn thành</th>
                                    <th>Điểm TB</th>
                                    <th>Điểm cao nhất</th>
                                    <th>Điểm thấp nhất</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subjectStats as $index => $stat)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $stat->subject_name }}</td>
                                    <td>{{ $stat->subject_code }}</td>
                                    <td>{{ $stat->total_students }}</td>
                                    <td>
                                        {{ $stat->completed }} / {{ $stat->total_students }}
                                        ({{ number_format(($stat->completed / $stat->total_students) * 100, 1) }}%)
                                    </td>
                                    <td>{{ number_format($stat->avg_score, 2) }}</td>
                                    <td>{{ number_format($stat->max_score, 2) }}</td>
                                    <td>{{ number_format($stat->min_score, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-subject-details" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#subjectDetailModal"
                                               data-subject-id="{{ $examPeriod->examPeriodSubjects->where('subject.name', $stat->subject_name)->first()?->id }}"
                                               data-subject-name="{{ $stat->subject_name }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết môn thi -->
<div class="modal fade" id="subjectDetailModal" tabindex="-1" aria-labelledby="subjectDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectDetailModalLabel">Chi tiết môn thi: <span id="modalSubjectName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3" id="loadingIndicator">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
                <div id="subjectDetailContent" class="d-none">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="#" id="exportExcelLink" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="#" id="exportWordLink" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-word me-1"></i> Word
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Xếp hạng</th>
                                    <th>SBD</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Phòng thi</th>
                                    <th>Số câu đúng</th>
                                    <th>Điểm</th>
                                </tr>
                            </thead>
                            <tbody id="subjectDetailTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Xử lý khi click vào nút xem chi tiết
    $('.view-subject-details').on('click', function() {
        const subjectId = $(this).data('subject-id');
        const subjectName = $(this).data('subject-name');
        
        // Hiển thị tên môn thi trong modal
        $('#modalSubjectName').text(subjectName);
        
        // Hiển thị loading, ẩn nội dung
        $('#loadingIndicator').removeClass('d-none');
        $('#subjectDetailContent').addClass('d-none');
        
        // Cập nhật URL cho các nút xuất file
        $('#exportExcelLink').attr('href', `{{ route('exam-reports.export', [$examPeriod, 'excel']) }}?report_type=subject&subject_id=${subjectId}`);
        $('#exportWordLink').attr('href', `{{ route('exam-reports.export', [$examPeriod, 'word']) }}?report_type=subject&subject_id=${subjectId}`);
        
        // Lấy dữ liệu chi tiết môn thi bằng AJAX
        $.ajax({
            url: `{{ route('exam-reports.by-subject', $examPeriod) }}`,
            data: { subject_id: subjectId, format: 'json' },
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Ẩn loading, hiển thị nội dung
                $('#loadingIndicator').addClass('d-none');
                $('#subjectDetailContent').removeClass('d-none');
                
                // Xóa dữ liệu cũ
                $('#subjectDetailTableBody').empty();
                
                // Thêm dữ liệu mới
                if (response.results && response.results.length > 0) {
                    response.results.forEach((result, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${result.student.exam_code || 'N/A'}</td>
                                <td>${result.student.student_code || 'N/A'}</td>
                                <td>${result.student.full_name || 'N/A'}</td>
                                <td>${result.exam_period_room?.room?.name || 'N/A'}</td>
                                <td>${result.correct_answers_after_review || result.correct_answers}/${result.total_questions}</td>
                                <td>${parseFloat(result.score_after_review || result.score).toFixed(2)}</td>
                            </tr>
                        `;
                        $('#subjectDetailTableBody').append(row);
                    });
                } else {
                    $('#subjectDetailTableBody').html('<tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>');
                }
            },
            error: function() {
                // Ẩn loading, hiển thị thông báo lỗi
                $('#loadingIndicator').addClass('d-none');
                $('#subjectDetailContent').removeClass('d-none');
                $('#subjectDetailTableBody').html('<tr><td colspan="7" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
            }
        });
    });
});
</script>
@endpush 