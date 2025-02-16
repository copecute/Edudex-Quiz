@extends('layouts.app')

@section('title', 'Quản lý thí sinh môn thi - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý thí sinh môn thi</h4>
            <p class="text-muted mb-0">
                Kỳ thi: {{ $testSession->name }} <br>
                Môn thi: {{ $subject->name }} ({{ $subject->code }}) <br>
                Ngành: {{ $subject->major->name }}
            </p>
        </div>
        <div>
            <a href="{{ route('test_sessions.subjects.index', $testSession) }}" 
               class="btn btn-light me-2">Quay lại</a>
            <button type="button" class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addStudentsModal">
                Thêm thí sinh
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($assignedStudents->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Chưa có thí sinh nào trong môn thi này</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>SBD</th>
                                <th>Mã SV</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Ngành học</th>
                                <th>Phòng thi</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedStudents as $student)
                                <tr>
                                    <td>{{ $student->testSessionSubjects[0]->pivot->exam_code }}</td>
                                    <td>{{ $student->code }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        @foreach($student->majors as $major)
                                            <span class="badge bg-{{ $major->pivot->is_main ? 'primary' : 'secondary' }} me-1">
                                                {{ $major->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @php
                                            $pivot = $student->testSessionSubjects[0]->pivot;
                                            $room = $pivot->test_shift_subject_room_id 
                                                ? $student->testSessionSubjects[0]->testShiftSubjectRooms->where('id', $pivot->test_shift_subject_room_id)->first()
                                                : null;
                                        @endphp
                                        @if($room)
                                            {{ $room->testRoom->name }}
                                            (Ca {{ $room->testShift->name }})
                                        @else
                                            <span class="text-danger">Chưa phân phòng</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('test_sessions.subjects.remove_student', [
                                                $testSession,
                                                $subject,
                                                $student
                                            ]) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $assignedStudents->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal thêm thí sinh -->
<div class="modal fade" id="addStudentsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('test_sessions.subjects.assign_students', [$testSession, $subject]) }}" 
                  method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm thí sinh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($availableStudents->isEmpty())
                        <div class="alert alert-info mb-0">
                            Không có thí sinh nào thuộc ngành {{ $subject->major->name }} có thể thêm vào môn thi này.
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Chọn thí sinh (Ngành {{ $subject->major->name }})</label>
                            <select name="student_ids[]" class="form-select select2-students" multiple required>
                                @foreach($availableStudents as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->name }} ({{ $student->code }})
                                        @if($student->majors->where('id', $subject->major_id)->first()->pivot->is_main)
                                            - Ngành chính
                                        @else
                                            - Ngành phụ
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Có thể chọn nhiều thí sinh cùng lúc
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    @if(!$availableStudents->isEmpty())
                        <button type="submit" class="btn btn-primary">Thêm thí sinh</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-students').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Chọn thí sinh',
        allowClear: true,
        dropdownParent: $('#addStudentsModal')
    });
});
</script>
@endpush
@endsection 