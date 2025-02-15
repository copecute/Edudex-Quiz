@extends('layouts.app')

@section('title', 'Quản lý phòng thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý phòng thi</h4>
            <p class="text-muted mb-0">Kỳ thi: {{ $testSession->name }}</p>
            <p class="text-muted mb-0">Ca thi: {{ $testShift->name }}</p>
            <p class="text-muted mb-0">
                Thời gian: {{ $testShift->start_time->format('H:i') }} - {{ $testShift->end_time->format('H:i') }}
                ngày {{ $testShift->test_date->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
           class="btn btn-light">Quay lại</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('test_sessions.test_shifts.test_rooms.update', [$testSession, $testShift]) }}" 
                  method="POST">
                @csrf
                @method('PUT')

                @if($availableRooms->isEmpty())
                    <div class="alert alert-warning">
                        Không có phòng thi nào khả dụng. 
                        Vui lòng kiểm tra lại trạng thái của các phòng thi và địa điểm thi.
                    </div>
                @else
                    @foreach($availableRooms as $locationName => $rooms)
                        <div class="mb-4">
                            <h5>{{ $locationName }}</h5>
                            <div class="row g-3">
                                @foreach($rooms as $room)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="rooms[]" 
                                                   value="{{ $room->id }}" 
                                                   class="form-check-input"
                                                   id="room_{{ $room->id }}"
                                                   {{ in_array($room->id, $assignedRooms) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="room_{{ $room->id }}">
                                                {{ $room->name }} ({{ $room->code }})
                                                <br>
                                                <small class="text-muted">{{ $room->capacity }} thí sinh</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @error('rooms')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">Cập nhật danh sách phòng thi</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection 