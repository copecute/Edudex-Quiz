@extends('layouts.app')

@section('title', 'Quản lý môn thi - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Thêm CSS cho bảng */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        margin-bottom: 0;
        white-space: nowrap;
    }
    .table td {
        vertical-align: middle;
    }
    /* Đặt chiều rộng cố định cho các cột */
    .table th:nth-child(1) { min-width: 100px; } /* Mã môn */
    .table th:nth-child(2) { min-width: 200px; } /* Tên môn */
    .table th:nth-child(3) { min-width: 100px; } /* Số ca thi */
    .table th:nth-child(4) { min-width: 200px; } /* Phòng thi */
    .table th:nth-child(5) { min-width: 200px; } /* Đề thi */
    .table th:nth-child(6) { min-width: 100px; } /* Thao tác */

</style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý môn thi</h4>
            <p class="text-muted mb-0">Kỳ thi: {{ $testSession->name }}</p>
        </div>
        <div>
            <a href="{{ route('test_sessions.index') }}" class="btn btn-light me-2">Quay lại</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                Thêm môn thi
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Thêm form tìm kiếm và lọc -->
            <form action="{{ route('test_sessions.subjects.index', $testSession) }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Tìm theo mã hoặc tên môn"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Trạng thái đề thi</option>
                        <option value="has_test_paper" {{ request('status') == 'has_test_paper' ? 'selected' : '' }}>
                            Đã phân đề thi
                        </option>
                        <option value="no_test_paper" {{ request('status') == 'no_test_paper' ? 'selected' : '' }}>
                            Chưa phân đề thi
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="has_room" class="form-select">
                        <option value="">Trạng thái phòng thi</option>
                        <option value="1" {{ request('has_room') == '1' ? 'selected' : '' }}>
                            Đã phân phòng
                        </option>
                        <option value="0" {{ request('has_room') == '0' ? 'selected' : '' }}>
                            Chưa phân phòng
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="shift" class="form-select">
                        <option value="">Tất cả ca thi</option>
                        @foreach($testSession->testShifts as $shift)
                            <option value="{{ $shift->id }}" {{ request('shift') == $shift->id ? 'selected' : '' }}>
                                Ca {{ $shift->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-search me-1"></i>
                        </button>
                        @if(request()->hasAny(['search', 'status', 'has_room', 'shift']))
                            <a href="{{ route('test_sessions.subjects.index', $testSession) }}" 
                               class="btn btn-light">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            @if($subjects->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Chưa có môn thi nào trong kỳ thi này</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã môn</th>
                                <th>Tên môn</th>
                                <th>Số ca thi</th>
                                <th>Phòng thi</th>
                                <th>Đề thi</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                            <tr>
                                <td>{{ $subject->code }}</td>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->test_shifts_count }}</td>
                                <td>
                                    @foreach($subject->testShiftSubjectRooms as $subjectRoom)
                                        <div>
                                            Ca {{ $subjectRoom->testShift->name }}:
                                            {{ $subjectRoom->testRoom->name }} ({{ $subjectRoom->testRoom->code }})
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    @if($subject->testSessionSubjects->first()->test_paper_id)
                                        {{ $subject->testSessionSubjects->first()->testPaper->name ?? 'Chưa phân đề thi' }}
                                    @else
                                        Chưa phân đề thi
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#assignShiftModal-{{ $subject->id }}">
                                            <i class="fas fa-clock me-1"></i>
                                            Ca thi
                                        </button>

                                        <button type="button" 
                                                class="btn btn-sm btn-info text-white" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#assignRoomModal-{{ $subject->id }}">
                                            <i class="fas fa-door-open me-1"></i>
                                            Phòng
                                        </button>

                                        <button type="button" 
                                                class="btn btn-sm btn-secondary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#assignTestPaperModal-{{ $subject->id }}">
                                            <i class="fas fa-file-alt me-1"></i>
                                            Đề thi
                                        </button>

                                        <a href="{{ route('test_sessions.subjects.students', [$testSession, $subject]) }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-users me-1"></i>
                                            Thí sinh
                                        </a>

                                        <form action="{{ route('test_sessions.subjects.destroy', [$testSession, $subject]) }}" 
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash me-1"></i>
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal phân phòng cho môn học -->
                            <div class="modal fade" id="assignRoomModal-{{ $subject->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Phân phòng thi - {{ $subject->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @php
                                                $testSessionSubject = $subject->testSessionSubjects->first();
                                                $assignedShifts = $testSessionSubject ? $testSessionSubject->testShifts : collect([]);
                                            @endphp

                                            @if($assignedShifts->isEmpty())
                                                <div class="alert alert-warning">
                                                    Môn thi này chưa được phân vào ca thi nào.
                                                    Vui lòng phân ca thi trước khi phân phòng.
                                                </div>
                                            @else
                                                @foreach($assignedShifts as $testShift)
                                                    <div class="mb-3">
                                                        <h6>Ca thi: {{ $testShift->name }}</h6>
                                                        <p class="text-muted small">
                                                            {{ $testShift->start_time->format('d/m/Y H:i') }} - 
                                                            {{ $testShift->end_time->format('H:i') }}
                                                        </p>
                                                        
                                                        @php
                                                            $subjectRoom = $testShift->testShiftSubjectRooms
                                                                ->where('test_session_subject_id', $testSessionSubject->id)
                                                                ->first();
                                                        @endphp

                                                        @if($subjectRoom)
                                                            <div class="d-flex align-items-center">
                                                                <span class="me-2">
                                                                    Phòng: {{ $subjectRoom->testRoom->name }}
                                                                </span>
                                                                <form action="{{ route('test_sessions.test_shifts.subject_rooms.destroy', [
                                                                    $testSession, 
                                                                    $testShift, 
                                                                    $subjectRoom
                                                                ]) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                                        Xóa
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <form action="{{ route('test_sessions.test_shifts.subject_rooms.store', [
                                                                $testSession, 
                                                                $testShift
                                                            ]) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="test_session_subject_id" 
                                                                       value="{{ $testSessionSubject->id }}">
                                                                <div class="input-group">
                                                                    <select name="test_room_id" class="form-select">
                                                                        <option value="">Chọn phòng thi</option>
                                                                        @foreach($availableRooms as $locationName => $rooms)
                                                                            <optgroup label="{{ $locationName }}">
                                                                                @foreach($rooms as $room)
                                                                                    <option value="{{ $room->id }}">
                                                                                        {{ $room->name }} ({{ $room->code }})
                                                                                        - {{ $room->capacity }} thí sinh
                                                                                    </option>
                                                                                @endforeach
                                                                            </optgroup>
                                                                        @endforeach
                                                                    </select>
                                                                    <button type="submit" class="btn btn-primary">
                                                                        Phân phòng
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal phân ca thi -->
                            <div class="modal fade" id="assignShiftModal-{{ $subject->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Phân ca thi - {{ $subject->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('test_sessions.subjects.assign_shifts', [$testSession, $subject]) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">Chọn ca thi</label>
                                                    @php
                                                        $testSessionSubject = $subject->testSessionSubjects->first();
                                                        $assignedShiftIds = $testSessionSubject ? $testSessionSubject->testShifts->pluck('id')->toArray() : [];
                                                    @endphp
                                                    <select name="test_shift_ids[]" class="form-select select2-multiple" multiple>
                                                        @foreach($testSession->testShifts as $shift)
                                                            <option value="{{ $shift->id }}" 
                                                                {{ in_array($shift->id, $assignedShiftIds) ? 'selected' : '' }}>
                                                                {{ $shift->name }} 
                                                                ({{ $shift->start_time->format('d/m/Y H:i') }} - {{ $shift->end_time->format('H:i') }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">
                                                        Có thể chọn nhiều ca thi
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary">Lưu</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal phân đề thi cho từng môn -->
                            <div class="modal fade" id="assignTestPaperModal-{{ $subject->id }}" 
                                 tabindex="-1" 
                                 aria-labelledby="assignTestPaperModalLabel-{{ $subject->id }}" 
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('test_sessions.subjects.assign_test_paper', [
                                                $testSession, 
                                                $subject->testSessionSubjects->first()
                                            ]) }}" 
                                              method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="assignTestPaperModalLabel-{{ $subject->id }}">
                                                    Phân đề thi cho môn {{ $subject->name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="test_paper_id_{{ $subject->id }}" class="form-label">Chọn đề thi</label>
                                                    <select name="test_paper_id" 
                                                            id="test_paper_id_{{ $subject->id }}" 
                                                            class="form-select" 
                                                            required>
                                                        <option value="">Chọn đề thi</option>
                                                        @foreach($subject->available_test_papers as $testPaper)
                                                            <option value="{{ $testPaper->id }}"
                                                                {{ $subject->testSessionSubjects->first()->test_paper_id == $testPaper->id ? 'selected' : '' }}>
                                                                {{ $testPaper->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary">Phân đề thi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal thêm môn thi -->
@include('test_sessions.subjects._add_subject_modal')
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