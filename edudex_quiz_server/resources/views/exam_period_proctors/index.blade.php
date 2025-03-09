@extends('layouts.app')

@section('title', 'Quản lý cán bộ coi thi')

@push('scripts')
<script src="/js/jquery-3.7.1.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận xóa một CBCT
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn xóa?')) {
            this.submit();
        }
    });

    // Chọn tất cả
    $('#selectAll').on('change', function() {
        $('.select-item').prop('checked', $(this).prop('checked'));
        updateDeleteButton();
    });

    // Cập nhật trạng thái nút xóa
    $('.select-item').on('change', function() {
        updateDeleteButton();
    });

    function updateDeleteButton() {
        const selectedIds = $('.select-item:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length > 0) {
            $('#deleteSelected').show();
            $('#selectedCount').text(selectedIds.length);
        } else {
            $('#deleteSelected').hide();
        }
    }

    // Xử lý xóa nhiều
    $('#deleteSelected').on('click', function() {
        const selectedIds = $('.select-item:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Vui lòng chọn ít nhất một cán bộ coi thi để xóa');
            return;
        }

        if (confirm(`Bạn có chắc chắn muốn xóa ${selectedIds.length} cán bộ coi thi đã chọn?`)) {
            $('input[name="proctor_ids"]').val(JSON.stringify(selectedIds));
            $('#deleteMultipleForm').submit();
        }
    });
});
</script>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Cán bộ coi thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách cán bộ coi thi</h5>
                    <div>
                        <a href="{{ route('exam-period-proctors.tools', $examPeriod) }}" class="btn btn-success me-2">
                            <i class="fas fa-file-excel me-1"></i> Nhập/Xuất
                        </a>
                        <a href="{{ route('exam-period-proctors.assign', $examPeriod) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Phân công
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('exam-period-proctors.index', $examPeriod) }}" method="GET" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Tìm kiếm theo tên, username, số điện thoại..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Form xóa nhiều -->
                    <form id="deleteMultipleForm" action="{{ route('exam-period-proctors.destroy-multiple', $examPeriod) }}" 
                          method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="proctor_ids" value="">
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Username</th>
                                    <th>Họ và tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Vai trò</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proctors as $proctor)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input select-item" 
                                               value="{{ $proctor->id }}">
                                    </td>
                                    <td>{{ $proctor->username }}</td>
                                    <td>{{ $proctor->fullName }}</td>
                                    <td>{{ $proctor->email }}</td>
                                    <td>{{ $proctor->phoneNumber }}</td>
                                    <td>
                                        @if($proctor->role == 0)
                                            <span class="badge bg-info">Cán bộ</span>
                                        @elseif($proctor->role == 1)
                                            <span class="badge bg-primary">Giáo viên</span>
                                        @elseif($proctor->role == 2)
                                            <span class="badge bg-success">Quản trị viên</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('exam-period-proctors.destroy', ['examPeriod' => $examPeriod->id, 'proctor' => $proctor->id]) }}" 
                                              method="POST" 
                                              class="d-inline delete-form">
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
                                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button id="deleteSelected" class="btn btn-danger" style="display: none;">
                        <i class="fas fa-trash-alt me-1"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                    </button>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $proctors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 