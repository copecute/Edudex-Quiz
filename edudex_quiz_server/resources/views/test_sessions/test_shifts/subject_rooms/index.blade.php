@extends('layouts.app')

@section('title', 'Quản lý phòng thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Quản lý phòng thi cho từng môn</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0">Ca thi: {{ $testShift->name }}</p>
                        <p class="mb-0">Thời gian: {{ $testShift->start_time->format('d/m/Y H:i') }} - {{ $testShift->end_time->format('H:i') }}</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Danh sách môn thi và phòng thi đã phân -->
                    <h6 class="mb-3">Phân công hiện tại:</h6>
                    <div class="table-responsive mb-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Môn thi</th>
                                    <th>Phòng thi</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($testShift->testSessionSubjects as $subject)
                                    <tr>
                                        <td>{{ $subject->subject->name }} ({{ $subject->subject->code }})</td>
                                        <td>
                                            @if($subjectRoom = $testShift->testShiftSubjectRooms->where('test_session_subject_id', $subject->id)->first())
                                                {{ $subjectRoom->testRoom->name }} ({{ $subjectRoom->testRoom->code }})
                                            @else
                                                <span class="text-muted">Chưa phân phòng</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subjectRoom)
                                                <form action="{{ route('test_sessions.test_shifts.subject_rooms.destroy', [$testSession, $testShift, $subjectRoom]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                        Xóa
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('test_sessions.test_shifts.subject_rooms.store', [$testSession, $testShift]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="test_session_subject_id" value="{{ $subject->id }}">
                                                    <select name="test_room_id" class="form-select form-select-sm d-inline-block w-auto me-2">
                                                        <option value="">Chọn phòng thi</option>
                                                        @foreach($availableRooms as $locationName => $rooms)
                                                            <optgroup label="{{ $locationName }}">
                                                                @foreach($rooms as $room)
                                                                    <option value="{{ $room->id }}">
                                                                        {{ $room->name }} ({{ $room->code }}) - {{ $room->capacity }} thí sinh
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-primary">Phân phòng</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                           class="btn btn-light">Quay lại</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 