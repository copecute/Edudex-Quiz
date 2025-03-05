@extends('layouts.app')

@section('title', 'Kết quả thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item active">Kết quả thi</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kết quả thi</h5>
                </div>
                <div class="card-body">
                    <!-- Form tìm kiếm -->
                    <form class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Tìm theo SBD, mã SV, họ tên..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Chọn môn thi --</option>
                                    @foreach($examPeriod->examPeriodSubjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="shift_id" class="form-select">
                                    <option value="">-- Chọn ca thi --</option>
                                    @foreach($examPeriod->examShifts as $shift)
                                        <option value="{{ $shift->id }}"
                                            {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Thống kê -->
                    <div class="alert alert-info mb-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <strong>Tổng số thí sinh:</strong> {{ $results->total() }}
                            </div>
                            <div class="col-md-3">
                                <strong>Điểm trung bình:</strong>
                                {{ number_format($results->avg('score'), 2) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Điểm cao nhất:</strong>
                                {{ number_format($results->max('score'), 2) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Điểm thấp nhất:</strong>
                                {{ number_format($results->min('score'), 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Bảng kết quả -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Mã kỳ thi</th>
                                    <th>Mã ca thi</th>
                                    <th>Mã môn thi</th>
                                    <th>Mã đề thi</th>
                                    <th>Mã phòng</th>
                                    <th>Mã CBCT</th>
                                    <th>SBD</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Số câu đúng</th>
                                    <th>Tổng số câu</th>
                                    <th>Điểm</th>
                                    <th>Ghi chú</th>
                                    <th>Log</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td>{{ $result->exam_period_code }}</td>
                                    <td>{{ $result->exam_shift_code }}</td>
                                    <td>{{ $result->exam_subject_code }}</td>
                                    <td>{{ $result->exam_code }}</td>
                                    <td>{{ $result->room_code }}</td>
                                    <td>{{ $result->proctor_code }}</td>
                                    <td>{{ $result->student->exam_code }}</td>
                                    <td>{{ $result->student->student_code }}</td>
                                    <td>{{ $result->student->full_name }}</td>
                                    <td>
                                        {{ $result->correct_answers_after_review ?? $result->correct_answers }}
                                    </td>
                                    <td>{{ $result->total_questions }}</td>
                                    <td>
                                        {{ $result->score_after_review ?? $result->score }}
                                    </td>
                                    <td>{{ $result->note }}</td>
                                    <td>
                                        <a href="data:application/octet-stream;base64,{{ $result->log_file }}"
                                           download="{{ $result->student->exam_code }}_{{ $result->student->student_code }}.edudex"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $results->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 