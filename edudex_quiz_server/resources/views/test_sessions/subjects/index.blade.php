@extends('layouts.app')

@section('title', 'Quản lý môn thi - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#assignShiftModal-{{ $subject->id }}">
                                        Phân ca thi
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#assignRoomModal-{{ $subject->id }}">
                                        Phân phòng
                                    </button>
                                    <form action="{{ route('test_sessions.subjects.destroy', [$testSession, $subject]) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
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