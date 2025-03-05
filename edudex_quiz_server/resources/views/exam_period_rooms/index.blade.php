@extends('layouts.app')

@section('title', 'Quản lý phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Phòng thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            @if(!$hasRooms)
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một phòng thi trước khi phân công. 
                    <a href="{{ route('rooms.index') }}" class="alert-link">Thêm phòng thi mới</a>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách phòng thi</h5>
                    <div>
                        <a href="{{ route('exam-period-rooms.tools', $examPeriod) }}" 
                           class="btn btn-success {{ !$hasRooms ? 'disabled' : '' }}"
                           {{ !$hasRooms ? 'aria-disabled=true tabindex=-1' : '' }}>
                            <i class="fas fa-file-excel me-1"></i> Nhập/Xuất
                        </a>
                        <a href="{{ route('exam-period-rooms.assign', $examPeriod) }}" 
                           class="btn btn-primary {{ !$hasRooms ? 'disabled' : '' }}"
                           {{ !$hasRooms ? 'aria-disabled=true tabindex=-1' : '' }}>
                            <i class="fas fa-plus-circle me-1"></i> Phân công phòng thi
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Form tìm kiếm và lọc -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm theo mã, tên phòng hoặc cơ sở..." 
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="facility_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Tất cả cơ sở</option>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}" 
                                    {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <!-- Form xóa nhiều -->
                    <form id="deleteMultipleForm" action="{{ route('exam-period-rooms.destroy-multiple', $examPeriod) }}" 
                          method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="room_ids[]">
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Mã phòng</th>
                                    <th>Tên phòng</th>
                                    <th>Cơ sở</th>
                                    <th>Sức chứa</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rooms as $examPeriodRoom)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input select-item" 
                                               value="{{ $examPeriodRoom->id }}">
                                    </td>
                                    <td>{{ $examPeriodRoom->room->code }}</td>
                                    <td>{{ $examPeriodRoom->room->name }}</td>
                                    <td>{{ $examPeriodRoom->room->facility->name }}</td>
                                    <td>{{ $examPeriodRoom->room->capacity }}</td>
                                    <td>
                                        <form method="POST" class="delete-form"
                                              action="{{ route('exam-period-rooms.destroy', $examPeriod) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="room_id" value="{{ $examPeriodRoom->room_id }}">
                                            <button type="submit" class="btn btn-danger btn-sm">
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

                    <!-- Nút xóa nhiều (ẩn mặc định) -->
                    <button id="deleteSelected" class="btn btn-danger" style="display: none;">
                        <i class="fas fa-trash-alt me-1"></i> Xóa đã chọn
                    </button>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $rooms->links() }}
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
        } else {
            $('#deleteSelected').hide();
        }
    }

    // Xử lý xóa nhiều
    $('#deleteSelected').on('click', function() {
        if (confirm('Bạn có chắc chắn muốn xóa các phòng thi đã chọn?')) {
            const selectedIds = $('.select-item:checked').map(function() {
                return $(this).val();
            }).get();

            $('input[name="room_ids[]"]').val(selectedIds);
            $('#deleteMultipleForm').submit();
        }
    });
});
</script> 