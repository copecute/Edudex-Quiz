@extends('layouts.app')

@section('title', 'Phân công môn thi - ca thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Phân công môn thi - ca thi</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Phân công môn thi vào ca thi</h5>
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

                    @if($subjects->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Chưa có môn thi nào được thêm vào kỳ thi này.
                            <a href="{{ route('exam-period-subjects.index', $examPeriod) }}" class="alert-link">
                                Thêm môn thi
                            </a>
                        </div>
                    @else
                        <form action="{{ route('exam-periods.assignment.subjects.store', $examPeriod) }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Môn thi</th>
                                            <th>Ca thi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjects as $subject)
                                        <tr>
                                            <td>
                                                {{ $subject->subject->name ?? 'N/A' }}
                                                <br>
                                                <small class="text-muted">{{ $subject->subject->code ?? '' }}</small>
                                                <input type="hidden" name="assignments[{{ $loop->index }}][subject_id]" 
                                                       value="{{ $subject->id }}">
                                            </td>
                                            <td>
                                                <select name="assignments[{{ $loop->index }}][shift_ids][]" 
                                                        class="form-select" 
                                                        multiple 
                                                        required
                                                        size="4">
                                                    @foreach($shifts as $shift)
                                                    <option value="{{ $shift->id }}"
                                                        {{ $subject->examShifts->contains($shift->id) ? 'selected' : '' }}>
                                                        {{ $shift->name }} ({{ $shift->start_time->format('H:i d/m/Y') }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fas fa-info-circle"></i>
                                                    Giữ Ctrl để chọn nhiều ca thi
                                                </small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

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