@extends('layouts.app')

@section('title', 'Quản lý ca thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Ca thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách ca thi</h5>
                    <a href="{{ route('exam-shifts.create', $examPeriod) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('exam-shifts.index', $examPeriod) }}" method="GET" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Tìm kiếm..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Khóa</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên ca thi</th>
                                    <th>Thời gian bắt đầu</th>
                                    <th>Thời gian kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shifts as $examShift)
                                <tr>
                                    <td>{{ $examShift->id }}</td>
                                    <td>
                                        {{ $examShift->name }}
                                        @if ($examShift->description)
                                            <i class="fas fa-info-circle text-info" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ $examShift->description }}"></i>
                                        @endif
                                    </td>
                                    <td>{{ $examShift->start_time->format('d/m/Y H:i') }}</td>
                                    <td>{{ $examShift->end_time->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('exam-shifts.toggle-status', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm status-btn 
                                                {{ $examShift->is_active ? 'btn-success' : 'btn-danger' }}">
                                                {{ $examShift->is_active ? 'Hoạt động' : 'Khóa' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('exam-shifts.edit', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('exam-shifts.destroy', ['examPeriod' => $examPeriod->id, 'examShift' => $examShift->id]) }}" 
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
                        {{ $shifts->links() }}
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
    // Xác nhận xóa
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn xóa?')) {
            this.submit();
        }
    });

    // Enable tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush 