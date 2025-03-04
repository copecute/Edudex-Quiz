@extends('layouts.app')

@section('title', 'Phân công phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Phân công phòng thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Phân công phòng thi</h5>
                    <div>
                        <a href="{{ route('exam-period-rooms.tools', $examPeriod) }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Nhập/Xuất
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-period-rooms.store', $examPeriod) }}" method="POST">
                        @csrf
                        
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab">
                                    Tất cả phòng thi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="selected-tab" data-bs-toggle="tab" href="#selected" role="tab">
                                    Đã chọn (<span id="selectedCount">0</span>)
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Tab tất cả -->
                            <div class="tab-pane fade show active" id="all" role="tabpanel">
                                <!-- Tìm kiếm và lọc -->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="searchInput" 
                                                   placeholder="Tìm kiếm theo mã, tên phòng hoặc cơ sở...">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="facilityFilter" class="form-select">
                                            <option value="">Tất cả cơ sở</option>
                                            @foreach($facilities as $facility)
                                            <option value="{{ $facility->name }}">{{ $facility->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Danh sách phòng -->
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                                </th>
                                                <th>Mã phòng</th>
                                                <th>Tên phòng</th>
                                                <th>Cơ sở</th>
                                                <th>Sức chứa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rooms as $room)
                                            <tr class="searchable-row">
                                                <td>
                                                    <input type="checkbox" name="room_ids[]" 
                                                           value="{{ $room->id }}" 
                                                           class="form-check-input room-checkbox"
                                                           data-code="{{ $room->code }}"
                                                           data-name="{{ $room->name }}"
                                                           data-facility="{{ $room->facility->name }}"
                                                           data-capacity="{{ $room->capacity }}"
                                                           {{ in_array($room->id, $assignedRoomIds) ? 'checked' : '' }}>
                                                </td>
                                                <td>{{ $room->code }}</td>
                                                <td>{{ $room->name }}</td>
                                                <td>{{ $room->facility->name }}</td>
                                                <td>{{ $room->capacity }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab đã chọn -->
                            <div class="tab-pane fade" id="selected" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="selectedTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input" id="selectedSelectAll">
                                                </th>
                                                <th>Mã phòng</th>
                                                <th>Tên phòng</th>
                                                <th>Cơ sở</th>
                                                <th>Sức chứa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sẽ được điền bởi JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-danger" id="removeSelected" style="display: none;">
                                    <i class="fas fa-trash me-1"></i> Bỏ chọn
                                </button>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('exam-period-rooms.index', $examPeriod) }}" 
                               class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Xử lý chọn tất cả trong tab tất cả
    $('#selectAll').change(function() {
        $('.room-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedTab();
    });

    // Xử lý chọn tất cả trong tab đã chọn
    $('#selectedSelectAll').change(function() {
        var isChecked = $(this).prop('checked');
        $('.selected-checkbox').prop('checked', isChecked);
    });

    // Cập nhật khi thay đổi checkbox
    $(document).on('change', '.room-checkbox', function() {
        updateSelectedTab();
    });

    // Xử lý nút bỏ chọn
    $('#removeSelected').click(function() {
        $('.selected-checkbox:checked').each(function() {
            var roomId = $(this).val();
            $('.room-checkbox[value="' + roomId + '"]').prop('checked', false);
        });
        updateSelectedTab();
    });

    // Hàm cập nhật tab đã chọn
    function updateSelectedTab() {
        var selectedRows = [];
        
        $('.room-checkbox:checked').each(function() {
            var $checkbox = $(this);
            selectedRows.push(`
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input selected-checkbox" 
                               value="${$checkbox.val()}">
                    </td>
                    <td>${$checkbox.data('code')}</td>
                    <td>${$checkbox.data('name')}</td>
                    <td>${$checkbox.data('facility')}</td>
                    <td>${$checkbox.data('capacity')}</td>
                </tr>
            `);
        });

        $('#selectedTable tbody').html(selectedRows.join(''));
        updateSelectedCount();
    }

    // Hàm cập nhật số lượng đã chọn
    function updateSelectedCount() {
        var count = $('.room-checkbox:checked').length;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#removeSelected').show();
        } else {
            $('#removeSelected').hide();
        }
    }

    // Khởi tạo ban đầu
    updateSelectedTab();
});
</script>
@endpush 