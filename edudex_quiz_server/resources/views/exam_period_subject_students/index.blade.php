@extends('layouts.app')

@section('title', 'Quản lý thí sinh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('exam-period-subjects.index', $examPeriod) }}">
                            Môn thi - {{ $examPeriod->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        Thí sinh - {{ $examPeriodSubject->subject->name }}
                    </li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách thí sinh</h5>
                    <div>
                        <a href="{{ route('exam-period-subject-students.tools', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                           class="btn btn-secondary me-2">
                            <i class="fas fa-file-import me-1"></i> Nhập/Xuất
                        </a>
                        <a href="{{ route('exam-period-subject-students.create', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm mới
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('exam-period-subject-students.index', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id]) }}" 
                          method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm kiếm theo mã, tên..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
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
                                        <a href="{{ route('exam-period-subject-students.edit', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id, 'student' => $student->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('exam-period-subject-students.destroy', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id, 'student' => $student->id]) }}" 
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có dữ liệu</td>
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