@extends('layouts.app')

@section('title', 'Quản lý thí sinh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Quản lý thí sinh</li>
                </ol>
            </nav>

            @if($examPeriod->examPeriodSubjects->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một môn thi trước khi thêm thí sinh. 
                    <a href="{{ route('exam-period-subjects.index', $examPeriod) }}" class="alert-link">Thêm môn thi mới</a>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách thí sinh</h5>
                    <div>
                        <a href="{{ route('students.tools', $examPeriod) }}"
                           class="btn btn-secondary me-2 {{ $examPeriod->examPeriodSubjects->isEmpty() ? 'disabled' : '' }}"
                               {{ $examPeriod->examPeriodSubjects->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                            <i class="fas fa-file-import me-1"></i> Nhập/Xuất
                        </a>
                        <a href="{{ route('students.create', $examPeriod) }}" class="btn btn-primary {{ $examPeriod->examPeriodSubjects->isEmpty() ? 'disabled' : '' }}"
                               {{ $examPeriod->examPeriodSubjects->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                            <i class="fas fa-plus me-1"></i> Thêm mới
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('students.index', $examPeriod) }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Tìm kiếm theo mã, tên..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="subject_id" class="form-select">
                                    <option value="">Tất cả môn thi</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="gender" class="form-select">
                                    <option value="">Tất cả giới tính</option>
                                    <option value="1" {{ request('gender') === '1' ? 'selected' : '' }}>Nam</option>
                                    <option value="0" {{ request('gender') === '0' ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-filter me-1"></i> Lọc
                                    </button>
                                    <a href="{{ route('students.index', $examPeriod) }}" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã dự thi</th>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Giới tính</th>
                                    <th>Ngày sinh</th>
                                    <th>Môn thi</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td>{{ $student->exam_code }}</td>
                                    <td>{{ $student->student_code }}</td>
                                    <td>{{ $student->full_name }}</td>
                                    <td>{{ $student->gender ? 'Nam' : 'Nữ' }}</td>
                                    <td>{{ $student->birthday ? $student->birthday->format('d/m/Y') : '' }}</td>
                                    <td>
                                        {{ $student->examPeriodSubject->subject->name }}
                                    </td>
                                    <td>
                                        <a href="{{ route('students.edit', [$examPeriod, $student]) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('students.destroy', $examPeriod) }}"
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn xóa?')) {
            this.submit();
        }
    });
});
</script>
@endpush 