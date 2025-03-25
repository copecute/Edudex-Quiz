@extends('layouts.app')

@section('title', 'Báo cáo theo phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-reports.index', $examPeriod) }}">Báo cáo kết quả thi</a></li>
                    <li class="breadcrumb-item active">Báo cáo theo phòng thi</li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thống kê theo phòng thi</h5>
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
                                    <th>Phòng thi</th>
                                    <th>Cơ sở</th>
                                    <th>Số thí sinh</th>
                                    <th>Điểm TB</th>
                                    <th>Điểm cao nhất</th>
                                    <th>Điểm thấp nhất</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roomStats as $index => $stat)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $stat->room_name }}</td>
                                    <td>{{ $stat->facility_name }}</td>
                                    <td>{{ $stat->total_students }}</td>
                                    <td>{{ number_format($stat->avg_score, 2) }}</td>
                                    <td>{{ number_format($stat->max_score, 2) }}</td>
                                    <td>{{ number_format($stat->min_score, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-room-details" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#roomDetailModal"
                                               data-room-id="{{ $examPeriod->examPeriodRooms->where('room.name', $stat->room_name)->first()?->id }}"
                                               data-room-name="{{ $stat->room_name }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($roomDetail)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi tiết phòng: {{ $roomDetail->room->name }}</h5>
                    <div>
                        <a href="{{ route('exam-reports.export', [$examPeriod, 'excel']) }}?report_type=room&room_id={{ $roomDetail->id }}" 
                           class="btn btn-sm btn-success me-2">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('exam-reports.export', [$examPeriod, 'word']) }}?report_type=room&room_id={{ $roomDetail->id }}" 
                           class="btn btn-sm btn-danger">
                            <i class="fas fa-file-word me-1"></i> Word
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>SBD</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Môn thi</th>
                                    <th>Số câu đúng</th>
                                    <th>Điểm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roomResults as $index => $result)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $result->student->exam_code }}</td>
                                    <td>{{ $result->student->student_code }}</td>
                                    <td>{{ $result->student->full_name }}</td>
                                    <td>{{ $result->examPeriodSubject->subject->name }}</td>
                                    <td>
                                        {{ $result->correct_answers_after_review ?? $result->correct_answers }}/
                                        {{ $result->total_questions }}
                                    </td>
                                    <td>{{ number_format($result->score_after_review ?? $result->score, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal chi tiết phòng thi -->
<div class="modal fade" id="roomDetailModal" tabindex="-1" aria-labelledby="roomDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomDetailModalLabel">Chi tiết phòng thi: <span id="modalRoomName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3" id="loadingIndicator">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
                <div id="roomDetailContent" class="d-none">
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
                                    <th>STT</th>
                                    <th>SBD</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Môn thi</th>
                                    <th>Số câu đúng</th>
                                    <th>Điểm</th>
                                </tr>
                            </thead>
                            <tbody id="roomDetailTableBody">
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
    $('.view-room-details').on('click', function() {
        const roomId = $(this).data('room-id');
        const roomName = $(this).data('room-name');
        
        // Hiển thị tên phòng thi trong modal
        $('#modalRoomName').text(roomName);
        
        // Hiển thị loading, ẩn nội dung
        $('#loadingIndicator').removeClass('d-none');
        $('#roomDetailContent').addClass('d-none');
        
        // Cập nhật URL cho các nút xuất file
        $('#exportExcelLink').attr('href', `{{ route('exam-reports.export', [$examPeriod, 'excel']) }}?report_type=room&room_id=${roomId}`);
        $('#exportWordLink').attr('href', `{{ route('exam-reports.export', [$examPeriod, 'word']) }}?report_type=room&room_id=${roomId}`);
        
        // Lấy dữ liệu chi tiết phòng thi bằng AJAX
        $.ajax({
            url: `{{ route('exam-reports.by-room', $examPeriod) }}`,
            data: { room_id: roomId, format: 'json' },
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Ẩn loading, hiển thị nội dung
                $('#loadingIndicator').addClass('d-none');
                $('#roomDetailContent').removeClass('d-none');
                
                // Xóa dữ liệu cũ
                $('#roomDetailTableBody').empty();
                
                // Thêm dữ liệu mới
                if (response.results && response.results.length > 0) {
                    response.results.forEach((result, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${result.student.exam_code || 'N/A'}</td>
                                <td>${result.student.student_code || 'N/A'}</td>
                                <td>${result.student.full_name || 'N/A'}</td>
                                <td>${result.exam_period_subject?.subject?.name || 'N/A'}</td>
                                <td>${result.correct_answers_after_review || result.correct_answers}/${result.total_questions}</td>
                                <td>${parseFloat(result.score_after_review || result.score).toFixed(2)}</td>
                            </tr>
                        `;
                        $('#roomDetailTableBody').append(row);
                    });
                } else {
                    $('#roomDetailTableBody').html('<tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>');
                }
            },
            error: function() {
                // Ẩn loading, hiển thị thông báo lỗi
                $('#loadingIndicator').addClass('d-none');
                $('#roomDetailContent').removeClass('d-none');
                $('#roomDetailTableBody').html('<tr><td colspan="7" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
            }
        });
    });
});
</script>
@endpush 