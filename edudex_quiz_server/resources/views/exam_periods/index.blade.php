@extends('layouts.app')

@section('title', 'Quản lý kỳ thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kỳ thi</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách kỳ thi</h5>
                    <div>
                        <a href="{{ route('exam-periods.tools') }}" class="btn btn-secondary">
                            <i class="fas fa-file-import me-1"></i> Import/Export
                        </a>
                        <a href="{{ route('exam-periods.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm mới
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('exam-periods.index') }}" method="GET" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
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
                                    <th>Tên kỳ thi</th>
                                    <th>Thời gian bắt đầu</th>
                                    <th>Thời gian kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($examPeriods as $examPeriod)
                                <tr>
                                    <td>{{ $examPeriod->id }}</td>
                                    <td>
                                        {{ $examPeriod->name }}
                                        @if ($examPeriod->description)
                                            <i class="fas fa-info-circle text-info" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ $examPeriod->description }}"></i>
                                        @endif
                                    </td>
                                    <td>{{ $examPeriod->start_time->format('d/m/Y H:i') }}</td>
                                    <td>{{ $examPeriod->end_time->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('exam-periods.toggle-status', $examPeriod) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm status-btn 
                                                {{ $examPeriod->is_active ? 'btn-success' : 'btn-danger' }}">
                                                {{ $examPeriod->is_active ? 'Hoạt động' : 'Khóa' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('exam-period-proctors.index', $examPeriod) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Quản lý cán bộ coi thi">
                                            <i class="fas fa-user-tie"></i>
                                        </a>
                                        <a href="{{ route('exam-period-subjects.index', $examPeriod) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Quản lý môn thi">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="{{ route('exam-shifts.index', $examPeriod) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Quản lý ca thi">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                        <a href="{{ route('exam-periods.edit', $examPeriod) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('exam-periods.destroy', $examPeriod) }}" 
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
                        {{ $examPeriods->links() }}
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