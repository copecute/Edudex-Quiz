@extends('layouts.app')

@section('title', 'Quản lý địa điểm thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý địa điểm thi</h4>
        <a href="{{ route('test_locations.create') }}" class="btn btn-primary">Thêm địa điểm mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('test_locations.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-8">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo tên hoặc địa chỉ">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            Đang hoạt động
                        </option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                            Đã khóa
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($testLocations->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('status'))
                        <a href="{{ route('test_locations.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên cơ sở</th>
                                <th>Địa chỉ</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testLocations as $testLocation)
                            <tr>
                                <td>
                                    {{ $testLocation->name }}
                                    @if($testLocation->description)
                                        <i class="fas fa-info-circle text-info" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $testLocation->description }}"></i>
                                    @endif
                                </td>
                                <td>{{ $testLocation->address }}</td>
                                <td>
                                    <span class="badge bg-{{ $testLocation->is_active ? 'success' : 'secondary' }}">
                                        {{ $testLocation->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('test_rooms.index', ['location' => $testLocation->id]) }}" 
                                       class="btn btn-sm btn-info">Phòng thi</a>
                                    <form action="{{ route('test_locations.toggle-status', $testLocation) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-{{ $testLocation->is_active ? 'warning' : 'success' }}">
                                            {{ $testLocation->is_active ? 'Khóa' : 'Mở khóa' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('test_locations.edit', $testLocation) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('test_locations.destroy', $testLocation) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $testLocations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Khởi tạo tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
@endsection 