@extends('layouts.app')

@section('title', 'Phân công cán bộ coi thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Phân công cán bộ coi thi</li>
                </ol>
            </nav>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Phân công cán bộ coi thi cho các phòng thi</h5>
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

                    <form action="{{ route('exam-periods.assignment.proctors.store', $examPeriod) }}" method="POST">
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
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Phòng thi</th>
                                                <th>Cán bộ coi thi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($shift->rooms as $shiftRoom)
                                            <tr>
                                                <td>
                                                    {{ $shiftRoom->room->name }}
                                                    @php
                                                        $pivotId = $shiftRoom->pivot->id ?? null;
                                                    @endphp
                                                    <input type="hidden" 
                                                           name="assignments[{{ $loop->parent->index }}_{{ $loop->index }}][shift_room_id]" 
                                                           value="{{ $pivotId }}">
                                                </td>
                                                <td>
                                                    <select name="assignments[{{ $loop->parent->index }}_{{ $loop->index }}][proctor_id]" 
                                                            class="form-select">
                                                        <option value="">Chọn CBCT</option>
                                                        @foreach($proctors as $proctor)
                                                        <option value="{{ $proctor->id }}"
                                                            {{ $shiftRoom->proctors->contains($proctor->id) ? 'selected' : '' }}>
                                                            {{ $proctor->account->accountInfo->fullName }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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