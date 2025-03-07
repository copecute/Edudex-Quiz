@extends('layouts.app')

@section('title', 'Phân công phòng thi')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a>
                        </li>
                        <li class="breadcrumb-item active">Phân công phòng thi</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <!-- Cột trái: Tổng quan môn thi -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tổng quan môn thi</h5>
                    </div>
                    <div class="card-body">
                        @if ($shifts->pluck('subjects')->flatten()->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Chưa có môn thi nào được phân công vào ca thi.
                                <a href="{{ route('exam-periods.assignment.subjects', $examPeriod) }}" class="alert-link">
                                    Phân công môn thi
                                </a>
                            </div>
                        @else
                            @foreach ($shifts->pluck('subjects')->flatten()->unique('id') as $subject)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $subject->subject->name }}</h6>
                                        <p class="mb-1">
                                            <i class="fas fa-users me-1"></i>
                                            Số thí sinh: {{ $subject->students->count() }}
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-clock me-1"></i>
                                            Ca thi:
                                            @foreach ($shifts as $shift)
                                                @if ($shift->subjects->contains($subject))
                                                    <span class="badge bg-secondary me-1">{{ $shift->name }}</span>
                                                @endif
                                            @endforeach
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Thông tin tổng số chỗ ngồi -->
                            <div class="alert mb-0 text-center" id="totalCapacityInfo">
                                <!-- Sẽ được cập nhật bởi JavaScript -->
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cột phải: Phân công phòng thi -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Phân công phòng thi</h5>
                        <div>
                            <a href="{{ route('exam-periods.assignment.auto', $examPeriod) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-laptop-code"></i>
                                Tự động
                            </a>
                            <a href="{{ route('exam-periods.assignment.rooms.export', $examPeriod) }}"
                                class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-file-excel me-1"></i>
                                Xuất danh sách
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($rooms->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Chưa có phòng thi nào được thêm vào kỳ thi này.
                                <a href="{{ route('exam-period-rooms.index', $examPeriod) }}" class="alert-link">
                                    Thêm phòng thi
                                </a>
                            </div>
                        @elseif($shifts->pluck('subjects')->flatten()->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Chưa phân công môn thi vào ca thi.
                                <a href="{{ route('exam-periods.assignment.subjects', $examPeriod) }}" class="alert-link">
                                    Phân công môn thi
                                </a>
                            </div>
                        @else
                            <form id="assignmentForm"
                                action="{{ route('exam-periods.assignment.rooms.store', $examPeriod) }}" method="POST">
                                @csrf
                                @foreach ($shifts as $shift)
                                    <div class="card mb-4" data-shift-id="{{ $shift->id }}">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">{{ $shift->name }}
                                                        ({{ $shift->start_time->format('H:i d/m/Y') }})</h6>
                                                    <small class="text-muted d-block mt-1">
                                                        <strong>Môn thi:</strong>
                                                        @foreach ($shift->subjects as $subject)
                                                            <span class="badge bg-info me-1">
                                                                {{ $subject->subject->name }}
                                                                ({{ $subject->students->count() }} thí sinh)
                                                            </span>
                                                        @endforeach
                                                    </small>
                                                </div>
                                                <div>
                                                    <select class="form-select form-select-sm assignment-type"
                                                        data-shift-id="{{ $shift->id }}" style="width: 200px;">
                                                        <option value="sequential">Theo thứ tự SBD</option>
                                                        <option value="random">Ngẫu nhiên</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($rooms as $room)
                                                    <div class="col-md-3 mb-3">
                                                        <div class="card">
                                                            <div class="card-body p-2">
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input room-checkbox"
                                                                        type="checkbox"
                                                                        data-room-index="{{ $loop->parent->index }}_{{ $loop->index }}"
                                                                        {{ $shift->rooms->contains($room->id) ? 'checked' : '' }}
                                                                        data-shift-id="{{ $shift->id }}"
                                                                        data-capacity="{{ $room->room->capacity }}"
                                                                        data-room-id="{{ $room->id }}"
                                                                        id="room_{{ $shift->id }}_{{ $room->id }}">
                                                                    <label class="form-check-label"
                                                                        for="room_{{ $shift->id }}_{{ $room->id }}">
                                                                        {{ $room->room->name }}
                                                                        <small class="d-block text-muted">
                                                                            ({{ $room->room->capacity }} chỗ ngồi)
                                                                        </small>
                                                                    </label>
                                                                </div>
                                                                <select
                                                                    class="form-select form-select-sm subject-select mb-2"
                                                                    data-room-index="{{ $loop->parent->index }}_{{ $loop->index }}"
                                                                    {{ !$shift->rooms->contains($room->id) ? 'disabled' : '' }}>
                                                                    <option value="">Chọn môn thi</option>
                                                                    @foreach ($shift->subjects as $subject)
                                                                        @php
                                                                            $roomInShift = $shift->rooms->firstWhere(
                                                                                'id',
                                                                                $room->id,
                                                                            );
                                                                            $isSelected =
                                                                                $roomInShift &&
                                                                                $roomInShift->pivot
                                                                                    ->exam_period_subject_id ==
                                                                                    $subject->id;
                                                                        @endphp
                                                                        <option value="{{ $subject->id }}"
                                                                            {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $subject->subject->name }}
                                                                            ({{ $subject->students->count() }} thí sinh)
                                                                        </option>
                                                                    @endforeach
                                                                </select>

                                                                <select class="form-select form-select-sm proctor-select"
                                                                    data-room-index="{{ $loop->parent->index }}_{{ $loop->index }}"
                                                                    {{ !$shift->rooms->contains($room->id) ? 'disabled' : '' }}>
                                                                    <option value="">Chọn cán bộ coi thi</option>
                                                                    @foreach ($proctors as $proctor)
                                                                        @php
                                                                            $roomInShift = $shift->rooms->firstWhere(
                                                                                'id',
                                                                                $room->id,
                                                                            );
                                                                            $isSelected =
                                                                                $roomInShift &&
                                                                                $roomInShift->pivot
                                                                                    ->exam_period_proctor_id ==
                                                                                    $proctor->id;
                                                                        @endphp
                                                                        <option value="{{ $proctor->id }}"
                                                                            {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $proctor->username }} -
                                                                            {{ $proctor->fullName }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Hidden inputs container -->
                                <div id="hiddenInputsContainer"></div>

                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Lưu phân công
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Lưu trữ thông tin số thí sinh cho mỗi môn thi
            const subjectStudents = {
                @foreach ($shifts->pluck('subjects')->flatten()->unique('id') as $subject)
                    {{ $subject->id }}: {{ $subject->students->count() }},
                @endforeach
            };

            // Hàm tính tổng sức chứa đã phân công cho một môn thi
            function getSubjectAssignedCapacity(subjectId) {
                let totalCapacity = 0;
                $('.room-checkbox:checked').each(function() {
                    const selectedSubjectId = $(this).closest('.card-body').find('.subject-select').val();
                    if (selectedSubjectId === subjectId) {
                        totalCapacity += parseInt($(this).data('capacity'));
                    }
                });
                return totalCapacity;
            }

            // Xử lý khi chọn môn thi
            $(document).on('change', '.subject-select', function() {
                const subjectId = $(this).val();
                if (!subjectId) return;

                const students = subjectStudents[subjectId];
                const assignedCapacity = getSubjectAssignedCapacity(subjectId);
                const roomCapacity = parseInt($(this).closest('.card-body').find('.room-checkbox').data(
                    'capacity'));

                if (assignedCapacity > students) {
                    const subject = $(this).find('option:selected').text().split('(')[0].trim();
                    const excess = assignedCapacity - students;
                    if (!confirm(
                            `Môn ${subject} đã có đủ chỗ ngồi cho ${students} thí sinh.\nPhòng này sẽ thừa ${excess} chỗ ngồi.\nBạn có chắc chắn muốn phân công thêm không?`
                            )) {
                        $(this).val('');
                        return;
                    }
                }

                updateTotalCapacityInfo();
            });

            // Hàm cập nhật thông tin tổng số chỗ ngồi
            function updateTotalCapacityInfo() {
                let totalStudents = 0;
                let totalCapacity = 0;
                let subjectCapacity = {};

                // Tính tổng số thí sinh
                Object.values(subjectStudents).forEach(count => {
                    totalStudents += count;
                });

                // Tính tổng sức chứa và sức chứa cho từng môn
                $('.room-checkbox:checked').each(function() {
                    const capacity = parseInt($(this).data('capacity'));
                    const subjectId = $(this).closest('.card-body').find('.subject-select').val();

                    if (subjectId) {
                        totalCapacity += capacity;
                        subjectCapacity[subjectId] = (subjectCapacity[subjectId] || 0) + capacity;
                    }
                });

                // Kiểm tra từng môn có đủ chỗ không
                let subjectWarnings = [];
                let excessWarnings = [];
                Object.entries(subjectStudents).forEach(([subjectId, students]) => {
                    const capacity = subjectCapacity[subjectId] || 0;
                    if (capacity < students) {
                        // Lấy tên môn từ select box môn thi
                        const subject = $('.subject-select option[value="' + subjectId + '"]')
                            .first()
                            .text()
                            .split('(')[0]
                            .trim();
                        subjectWarnings.push(`${subject}: thiếu ${students - capacity} chỗ`);
                    } else if (capacity > students) {
                        // Lấy tên môn từ select box môn thi
                        const subject = $('.subject-select option[value="' + subjectId + '"]')
                            .first()
                            .text()
                            .split('(')[0]
                            .trim();
                        excessWarnings.push(`${subject}: thừa ${capacity - students} chỗ`);
                    }
                });

                // Cập nhật hiển thị
                let alertClass = 'bg-success';
                let message = 'Đủ chỗ ngồi cho tất cả thí sinh';

                if (totalCapacity < totalStudents) {
                    alertClass = 'bg-danger';
                    message = `Thiếu ${totalStudents - totalCapacity} chỗ ngồi`;
                } else if (totalCapacity > totalStudents) {
                    alertClass = 'bg-secondary';
                    message = `Dư ${totalCapacity - totalStudents} chỗ ngồi`;
                }

                let html = `
            <div>
                <h5>Tổng số thí sinh: ${totalStudents} | Tổng sức chứa: ${totalCapacity}</h5>
                <div class="badge ${alertClass} fs-6 mb-2">${message}</div>
            </div>
        `;

                if (subjectWarnings.length > 0 || excessWarnings.length > 0) {
                    html += `
                <div class="mt-2">
                    ${subjectWarnings.length > 0 ? `
                            <div class="text-danger">
                                <small>Cảnh báo thiếu:<br>${subjectWarnings.join('<br>')}</small>
                            </div>
                        ` : ''}
                    ${excessWarnings.length > 0 ? `
                            <div class="text-warning">
                                <small>Cảnh báo thừa:<br>${excessWarnings.join('<br>')}</small>
                            </div>
                        ` : ''}
                </div>
            `;
                }

                $('#totalCapacityInfo').html(html);
            }

            // Cập nhật khi có thay đổi
            $(document).on('change', '.room-checkbox, .subject-select', function() {
                if ($(this).hasClass('room-checkbox')) {
                    const select = $(this).closest('.card-body').find('.subject-select');
                    const proctorSelect = $(this).closest('.card-body').find('.proctor-select');
                    select.prop('disabled', !this.checked);
                    proctorSelect.prop('disabled', !this.checked);
                    if (!this.checked) {
                        select.val('');
                        proctorSelect.val('');
                    }
                }
                updateTotalCapacityInfo();
            });

            // Cập nhật lần đầu khi trang load
            updateTotalCapacityInfo();

            // Hàm kiểm tra cán bộ coi thi đã được phân công trong ca thi chưa
            function isProctorAssignedInShift(shiftId, proctorId, excludeRoomIndex) {
                let isAssigned = false;
                $(`.card[data-shift-id="${shiftId}"] .proctor-select`).each(function(index) {
                    if (index !== excludeRoomIndex && $(this).val() === proctorId) {
                        isAssigned = true;
                        return false; // break loop
                    }
                });
                return isAssigned;
            }

            // Xử lý khi chọn cán bộ coi thi
            $(document).on('change', '.proctor-select', function() {
                const proctorId = $(this).val();
                if (!proctorId) return; // Bỏ qua nếu chưa chọn CBCT

                const shiftCard = $(this).closest('.card.mb-4');
                const shiftId = shiftCard.data('shift-id');
                const roomIndex = $(this).closest('.col-md-3').index();

                // Kiểm tra xem CBCT đã được phân công trong ca thi này chưa
                if (isProctorAssignedInShift(shiftId, proctorId, roomIndex)) {
                    alert('Cán bộ coi thi này đã được phân công cho phòng khác trong cùng ca thi');
                    $(this).val(''); // Reset về trạng thái chưa chọn
                    return;
                }
            });

            // Xử lý form submit
            $('#assignmentForm').on('submit', function(e) {
                e.preventDefault();

                // Xóa tất cả hidden inputs cũ
                $('#hiddenInputsContainer').empty();

                // Duyệt qua từng ca thi
                let hasCheckedRooms = false;
                let hasError = false;
                let assignmentIndex = 0;

                // Kiểm tra trùng CBCT trong cùng ca thi
                $('.card.mb-4').each(function() {
                    const shiftId = $(this).data('shift-id');
                    const assignedProctors = new Set();

                    $(this).find('.proctor-select').each(function() {
                        const proctorId = $(this).val();
                        if (proctorId && assignedProctors.has(proctorId)) {
                            alert(
                                'Một cán bộ coi thi không thể coi nhiều phòng trong cùng một ca thi');
                            hasError = true;
                            return false;
                        }
                        if (proctorId) {
                            assignedProctors.add(proctorId);
                        }
                    });

                    if (hasError) return false;
                });

                if (hasError) return false;

                $('.card.mb-4').each(function() {
                    const shiftId = $(this).data('shift-id'); // Lấy shift_id từ data attribute
                    let rooms = [];

                    // Duyệt qua các phòng được chọn trong ca thi
                    $(this).find('.room-checkbox:checked').each(function() {
                        hasCheckedRooms = true;
                        const roomId = $(this).data('room-id');
                        const subjectId = $(this).closest('.card-body').find(
                            '.subject-select').val();

                        if (!subjectId) {
                            alert('Vui lòng chọn môn thi cho tất cả các phòng được chọn');
                            hasError = true;
                            return false;
                        }

                        rooms.push({
                            room_id: roomId,
                            subject_id: subjectId,
                            proctor_id: parseInt($(this).closest('.card-body').find(
                                '.proctor-select').val()) || null
                        });
                    });

                    // Chỉ thêm dữ liệu nếu ca thi có phòng được chọn
                    if (rooms.length > 0) {
                        $('#hiddenInputsContainer').append(`
                    <input type="hidden" name="assignments[${assignmentIndex}][shift_id]" value="${shiftId}">
                `);

                        rooms.forEach((room, index) => {
                            $('#hiddenInputsContainer').append(`
                        <input type="hidden" name="assignments[${assignmentIndex}][rooms][${index}][room_id]" value="${room.room_id}">
                        <input type="hidden" name="assignments[${assignmentIndex}][rooms][${index}][subject_id]" value="${room.subject_id}">
                        <input type="hidden" name="assignments[${assignmentIndex}][rooms][${index}][proctor_id]" value="${room.proctor_id}">
                    `);
                        });

                        assignmentIndex++;
                    }
                });

                if (hasError) {
                    return false;
                }

                if (!hasCheckedRooms) {
                    alert('Vui lòng chọn ít nhất một phòng thi');
                    return false;
                }

                // Kiểm tra và thu thập thông tin phân công thí sinh
                const studentAssignments = [];
                $('.assignment-type').each(function() {
                    const type = $(this).val();
                    const shiftId = $(this).data('shift-id');
                    if (type) {
                        studentAssignments.push({
                            shift_id: shiftId,
                            assignment_type: type
                        });
                    }
                });

                // Thêm hidden inputs cho phân công thí sinh
                studentAssignments.forEach((assignment, index) => {
                    $('#hiddenInputsContainer').append(`
                <input type="hidden" name="student_assignments[${index}][shift_id]" value="${assignment.shift_id}">
                <input type="hidden" name="student_assignments[${index}][assignment_type]" value="${assignment.assignment_type}">
            `);
                });

                console.log('Form data:', $(this).serialize());
                this.submit();
            });
        });
    </script>
@endpush
