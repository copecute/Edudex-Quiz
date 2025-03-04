@extends('layouts.app')

@section('title', 'Phân công phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Phân công phòng thi</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Phân công phòng thi cho các ca thi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('exam-periods.assignment.rooms.store', $examPeriod) }}" method="POST">
                        @csrf
                        @foreach($shifts as $shift)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">{{ $shift->name }} ({{ $shift->start_time->format('H:i d/m/Y') }})</h6>
                                <small class="text-muted">
                                    Môn thi: 
                                    {{ $shift->subjects->pluck('subject.name')->implode(', ') }}
                                </small>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="assignments[{{ $loop->index }}][shift_id]" 
                                       value="{{ $shift->id }}">
                                
                                <div class="row">
                                    @foreach($rooms as $room)
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="assignments[{{ $loop->parent->index }}][room_ids][]" 
                                                   value="{{ $room->id }}"
                                                   {{ $shift->rooms->contains($room->id) ? 'checked' : '' }}
                                                   id="room_{{ $shift->id }}_{{ $room->id }}">
                                            <label class="form-check-label" 
                                                   for="room_{{ $shift->id }}_{{ $room->id }}">
                                                {{ $room->room->name }}
                                                <small class="d-block text-muted">
                                                    ({{ $room->room->capacity }} chỗ ngồi)
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu phân công
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 