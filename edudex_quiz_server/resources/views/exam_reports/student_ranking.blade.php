@extends('layouts.app')

@section('title', 'Báo cáo xếp hạng thí sinh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">{{ $examPeriod->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-reports.index', $examPeriod) }}">Báo cáo kết quả thi</a></li>
                    <li class="breadcrumb-item active">Báo cáo xếp hạng thí sinh</li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tìm kiếm</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('exam-reports.ranking', $examPeriod) }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Môn thi</label>
                                    <select name="subject_id" class="form-select">
                                        <option value="">Tất cả môn thi</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tìm kiếm</label>
                                    <input type="text" name="search" class="form-control" placeholder="Tên, mã SV..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Top thí sinh</label>
                                    <select name="limit" class="form-select">
                                        <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>Top 10</option>
                                        <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>Top 20</option>
                                        <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>Top 50</option>
                                        <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>Top 100</option>
                                        <option value="" {{ !request('limit') ? 'selected' : '' }}>Tất cả</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Lọc dữ liệu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Xếp hạng thí sinh 
                        @if(request('subject_id'))
                        - {{ $examPeriod->examPeriodSubjects->find(request('subject_id'))->subject->name }}
                        @else
                        - Tất cả môn thi
                        @endif
                    </h5>
                    <div>
                        <a href="{{ route('exam-reports.export', [$examPeriod, 'excel']) }}?report_type=ranking{{ request('subject_id') ? '&subject_id='.request('subject_id') : '' }}&search={{ request('search') }}&limit={{ request('limit') }}" 
                           class="btn btn-sm btn-success me-2">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('exam-reports.export', [$examPeriod, 'word']) }}?report_type=ranking{{ request('subject_id') ? '&subject_id='.request('subject_id') : '' }}&search={{ request('search') }}&limit={{ request('limit') }}" 
                           class="btn btn-sm btn-danger">
                            <i class="fas fa-file-word me-1"></i> Word
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Xếp hạng</th>
                                    <th>SBD</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Môn thi</th>
                                    <th>Phòng thi</th>
                                    <th>Số câu đúng</th>
                                    <th>Điểm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rankings as $index => $result)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                            <span class="badge rounded-pill bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }}">
                                                {{ $index + 1 }}
                                            </span>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>{{ $result->student->exam_code }}</td>
                                    <td>{{ $result->student->student_code }}</td>
                                    <td>{{ $result->student->full_name }}</td>
                                    <td>{{ $result->examPeriodSubject->subject->name }}</td>
                                    <td>{{ $result->examPeriodRoom->room->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $result->correct_answers_after_review ?? $result->correct_answers }}/
                                        {{ $result->total_questions }}
                                    </td>
                                    <td>{{ number_format($result->score_after_review ?? $result->score, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($rankings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-end mt-3">
                        {{ $rankings->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 