@extends('layouts.app')

@section('title', 'Quản lý ca thi - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý ca thi</h4>
            <p class="text-muted mb-0">Kỳ thi: {{ $testSession->name }}</p>
        </div>
        <div>
            <a href="{{ route('test_sessions.index') }}" class="btn btn-light me-2">Quay lại</a>
            <a href="{{ route('test_sessions.test_shifts.create', $testSession) }}" 
               class="btn btn-primary">Thêm ca thi mới</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                  method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo tên ca thi">
                </div>
                <div class="col-md-3">
                    <select name="subject" class="form-select">
                        <option value="">Tất cả môn thi</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" 
                                {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            Đang hoạt động
                        </option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                            Đã khóa
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($testShifts->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('status') || request('subject'))
                        <a href="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                           class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên ca thi</th>
                                <th>Môn thi</th>
                                <th>Ngày thi</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testShifts as $testShift)
                            <tr>
                                <td>
                                    {{ $testShift->name }}
                                    @if($testShift->description)
                                        <i class="fas fa-info-circle text-info" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $testShift->description }}"></i>
                                    @endif
                                </td>
                                <td>
                                    @foreach($testShift->testSessionSubjects as $testSessionSubject)
                                        <div>
                                            {{ $testSessionSubject->subject->name }} ({{ $testSessionSubject->subject->code }})
                                            @php
                                                $subjectRoom = $testShift->testShiftSubjectRooms
                                                    ->where('test_session_subject_id', $testSessionSubject->id)
                                                    ->first();
                                            @endphp
                                            @if($subjectRoom)
                                                <span class="text-muted">
                                                    - Phòng {{ $subjectRoom->testRoom->name }} ({{ $subjectRoom->testRoom->code }})
                                                </span>
                                            @else
                                                <span class="text-warning">
                                                    - Chưa phân phòng
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td>{{ $testShift->start_time->format('d/m/Y') }}</td>
                                <td>{{ $testShift->start_time->format('H:i') }} - {{ $testShift->end_time->format('H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $testShift->is_active ? 'success' : 'secondary' }}">
                                        {{ $testShift->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('test_sessions.test_shifts.toggle-status', [$testSession, $testShift]) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-{{ $testShift->is_active ? 'warning' : 'success' }}">
                                            {{ $testShift->is_active ? 'Khóa' : 'Mở khóa' }}
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#assignSubjectsModal-{{ $testShift->id }}">
                                        Phân môn thi
                                    </button>
                                    <a href="{{ route('test_sessions.test_shifts.subject_rooms.index', [$testSession, $testShift]) }}" 
                                       class="btn btn-sm btn-info">Phân phòng thi</a>
                                    <a href="{{ route('test_sessions.test_shifts.edit', [$testSession, $testShift]) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('test_sessions.test_shifts.destroy', [$testSession, $testShift]) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal phân môn thi cho mỗi ca thi -->
                            <div class="modal fade" id="assignSubjectsModal-{{ $testShift->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Phân môn thi - {{ $testShift->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('test_sessions.test_shifts.assign_subjects', [$testSession, $testShift]) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">Chọn môn thi</label>
                                                    <select name="test_session_subject_ids[]" class="form-select select2-multiple" multiple>
                                                        @foreach($testSession->testSessionSubjects as $testSessionSubject)
                                                            <option value="{{ $testSessionSubject->id }}" 
                                                                {{ $testShift->testSessionSubjects->contains($testSessionSubject->id) ? 'selected' : '' }}>
                                                                {{ $testSessionSubject->subject->name }} 
                                                                ({{ $testSessionSubject->subject->code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">
                                                        Có thể chọn nhiều môn thi
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

                <div class="mt-4">
                    {{ $testShifts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Khởi tạo tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Khởi tạo Select2 cho tất cả các select trong modal
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Chọn môn thi',
        allowClear: true,
        dropdownParent: $('.modal') // Để select2 hoạt động đúng trong modal
    });

    // Fix lỗi select2 trong modal Bootstrap
    $(document).on('shown.bs.modal', '.modal', function () {
        $(this).find('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Chọn môn thi',
            allowClear: true,
            dropdownParent: $(this)
        });
    });
});
</script>
@endpush
@endsection 