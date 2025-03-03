@extends('layouts.app')

@section('title', 'Phân công coi thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Phân công coi thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Phân công coi thi</h5>
                    <div>
                        <a href="{{ route('exam-period-proctors.tools', $examPeriod) }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Nhập/Xuất
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('exam-period-proctors.store', $examPeriod) }}" method="POST">
                        @csrf
                        
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab">
                                    Tất cả người dùng
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
                                <!-- Tìm kiếm -->
                                <div class="row mb-3">
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="searchInput" 
                                                   placeholder="Tìm kiếm theo tên, username, email...">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Danh sách người dùng -->
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                                </th>
                                                <th>Username</th>
                                                <th>Họ và tên</th>
                                                <th>Email</th>
                                                <th>Số điện thoại</th>
                                                <th>Vai trò</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($accounts as $account)
                                            <tr class="searchable-row">
                                                <td>
                                                    <input type="checkbox" name="account_ids[]" 
                                                           value="{{ $account->id }}" 
                                                           class="form-check-input account-checkbox"
                                                           @if($account->examPeriodProctors->count() > 0) checked @endif>
                                                </td>
                                                <td>{{ $account->username }}</td>
                                                <td>{{ $account->fullName }}</td>
                                                <td>{{ $account->email }}</td>
                                                <td>{{ $account->phoneNumber }}</td>
                                                <td>
                                                    @if($account->role == 0)
                                                        <span class="badge bg-info">Cán bộ</span>
                                                    @elseif($account->role == 1)
                                                        <span class="badge bg-primary">Giáo viên</span>
                                                    @elseif($account->role == 2)
                                                        <span class="badge bg-success">Quản trị viên</span>
                                                    @endif
                                                </td>
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
                                                <th>Username</th>
                                                <th>Họ và tên</th>
                                                <th>Email</th>
                                                <th>Số điện thoại</th>
                                                <th>Vai trò</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sẽ được điền bởi JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-danger" id="removeSelected">
                                    <i class="fas fa-trash me-1"></i> Bỏ chọn
                                </button>
                            </div>
                        </div>

                        @error('account_ids')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror

                        <div class="mt-3">
                            <a href="{{ route('exam-period-proctors.index', $examPeriod) }}" 
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
        $('.account-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedTab();
    });

    // Xử lý chọn tất cả trong tab đã chọn
    $('#selectedSelectAll').change(function() {
        var isChecked = $(this).prop('checked');
        $('.selected-checkbox').prop('checked', isChecked);
    });

    // Xử lý tìm kiếm
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.searchable-row').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Cập nhật khi thay đổi checkbox trong tab tất cả
    $(document).on('change', '.account-checkbox', function() {
        var allChecked = $('.account-checkbox:checked').length === $('.account-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
        updateSelectedTab();
    });

    // Xử lý nút bỏ chọn
    $('#removeSelected').click(function() {
        $('.selected-checkbox:checked').each(function() {
            var accountId = $(this).val();
            $('.account-checkbox[value="' + accountId + '"]').prop('checked', false);
        });
        updateSelectedTab();
    });

    // Xử lý khi thay đổi checkbox trong tab đã chọn
    $(document).on('change', '.selected-checkbox', function() {
        var accountId = $(this).val();
        var isChecked = $(this).prop('checked');
        $('.account-checkbox[value="' + accountId + '"]').prop('checked', isChecked);
        
        if (!isChecked) {
            $(this).closest('tr').remove();
            updateSelectedCount();
        }
    });

    // Hàm cập nhật tab đã chọn
    function updateSelectedTab() {
        var selectedRows = [];
        var count = 0;

        $('.account-checkbox:checked').each(function() {
            count++;
            var row = $(this).closest('tr');
            var accountId = $(this).val();
            
            selectedRows.push(`
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input selected-checkbox" 
                               value="${accountId}">
                    </td>
                    <td>${row.find('td:eq(1)').text()}</td>
                    <td>${row.find('td:eq(2)').text()}</td>
                    <td>${row.find('td:eq(3)').text()}</td>
                    <td>${row.find('td:eq(4)').text()}</td>
                    <td>${row.find('td:eq(5)').html()}</td>
                </tr>
            `);
        });

        $('#selectedTable tbody').html(selectedRows.join(''));
        updateSelectedCount();
    }

    // Hàm cập nhật số lượng đã chọn
    function updateSelectedCount() {
        var count = $('.account-checkbox:checked').length;
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