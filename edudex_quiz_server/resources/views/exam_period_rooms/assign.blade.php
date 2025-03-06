@extends('layouts.app')

@section('title', 'Phân công phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.dashboard', $examPeriod) }}">Kỳ thi</a></li>
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
                        
                        <!-- Hidden inputs để lưu các phòng đã chọn -->
                        <div id="selectedRoomInputs"></div>
                        
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
                                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
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
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Đang tải...</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pagination-container mt-3">
                                    {{ $rooms->links() }}
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
    // Lưu trữ thông tin phòng đã chọn
    let selectedRooms = new Map();
    
    // Khởi tạo với các phòng đã được phân công trước đó
    @foreach($assignedRooms as $room)
        selectedRooms.set('{{ $room['id'] }}', {
            id: '{{ $room['id'] }}',
            code: '{{ $room['code'] }}',
            name: '{{ $room['name'] }}',
            facility: '{{ $room['facility'] }}',
            capacity: {{ $room['capacity'] }}
        });
    @endforeach

    function loadRooms(page = 1) {
        let search = $('#searchInput').val();
        let facility = $('#facilityFilter').val();
        
        $.ajax({
            url: '{{ route('exam-period-rooms.assign', $examPeriod) }}',
            data: {
                search: search,
                facility_id: facility,
                page: page
            },
            success: function(response) {
                $('#all table tbody').html(response.html);
                $('.pagination-container').html(response.pagination);
               
                // Khôi phục trạng thái checkbox đã chọn
                $('.room-checkbox').each(function() {
                    $(this).prop('checked', selectedRooms.has($(this).val()));
                });
               
                updateSelectedTab();
                $('#selectAll').prop('checked', $('.room-checkbox:not(:checked)').length === 0);
            }
        });
    }

    // Load dữ liệu ban đầu và cập nhật tab đã chọn
    loadRooms();
    updateSelectedTab();

    // Xử lý tìm kiếm với debounce
    let searchTimer;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            loadRooms(1); // Reset về trang 1 khi tìm kiếm
        }, 300);
    });

    // Xử lý lọc theo cơ sở
    $('#facilityFilter').change(function() {
        loadRooms(1); // Reset về trang 1 khi lọc
    });

    // Xử lý phân trang
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        loadRooms(page);
        $('.table-responsive').scrollTop(0);
    });

    // Xử lý chọn tất cả trong tab tất cả
    $('#selectAll').change(function() {
        let isChecked = $(this).prop('checked');
        $('.room-checkbox').prop('checked', isChecked);
        // Cập nhật Map khi chọn/bỏ chọn tất cả
        $('.room-checkbox').each(function() {
            let $checkbox = $(this);
            if (isChecked) {
                selectedRooms.set($checkbox.val(), {
                    id: $checkbox.val(),
                    code: $checkbox.data('code'),
                    name: $checkbox.data('name'),
                    facility: $checkbox.data('facility'),
                    capacity: $checkbox.data('capacity')
                });
            } else {
                selectedRooms.delete($checkbox.val());
            }
        });
        updateSelectedTab();
    });

    // Xử lý chọn tất cả trong tab đã chọn
    $('#selectedSelectAll').change(function() {
        var isChecked = $(this).prop('checked');
        $('.selected-checkbox').prop('checked', isChecked);
    });

    // Cập nhật khi thay đổi checkbox
    $(document).on('change', '.room-checkbox', function() {
        let $checkbox = $(this);
        // Cập nhật Map khi checkbox thay đổi
        if ($checkbox.prop('checked')) {
            selectedRooms.set($checkbox.val(), {
                id: $checkbox.val(),
                code: $checkbox.data('code'),
                name: $checkbox.data('name'),
                facility: $checkbox.data('facility'),
                capacity: $checkbox.data('capacity')
            });
        } else {
            selectedRooms.delete($checkbox.val());
        }
        updateSelectedTab();
    });

    // Xử lý nút bỏ chọn
    $('#removeSelected').click(function() {
        $('.selected-checkbox:checked').each(function() {
            var roomId = $(this).val();
            $('.room-checkbox[value="' + roomId + '"]').prop('checked', false);
            selectedRooms.delete(roomId);
        });
        updateSelectedTab();
    });

    // Hàm cập nhật tab đã chọn
    function updateSelectedTab() {
        var selectedRows = [];
        
        // Lấy thông tin tất cả các phòng đã chọn từ Map
        selectedRooms.forEach(function(room) {
            selectedRows.push(`
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input selected-checkbox" 
                               value="${room.id}">
                    </td>
                    <td>${room.code}</td>
                    <td>${room.name}</td>
                    <td>${room.facility}</td>
                    <td>${room.capacity}</td>
                </tr>
            `);
        });

        $('#selectedTable tbody').html(selectedRows.join(''));
        $('#selectedCount').text(selectedRooms.size);
        $('#removeSelected').toggle(selectedRooms.size > 0);

        // Cập nhật hidden inputs cho form submit
        let hiddenInputs = '';
        selectedRooms.forEach(function(room) {
            hiddenInputs += `<input type="hidden" name="room_ids[]" value="${room.id}">`;
        });
        $('#selectedRoomInputs').html(hiddenInputs);
    }

    // Xử lý submit form
    $('form').on('submit', function(e) {
        if (selectedRooms.size === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một phòng thi');
            return false;
        }
    });
});
</script>
@endpush 