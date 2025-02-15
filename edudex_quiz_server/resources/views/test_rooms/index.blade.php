@extends('layouts.app')

@section('title', 'Quản lý phòng thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý phòng thi</h4>
        <a href="{{ route('test_rooms.create') }}" class="btn btn-primary">Thêm phòng thi mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('test_rooms.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <select name="location" class="form-select">
                        <option value="">Tất cả địa điểm</option>
                        @foreach($testLocations as $location)
                            <option value="{{ $location->id }}" 
                                {{ request('location') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo mã hoặc tên phòng">
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

            @if($testRooms->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('status') || request('location'))
                        <a href="{{ route('test_rooms.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã phòng</th>
                                <th>Tên phòng</th>
                                <th>Địa điểm</th>
                                <th>Sức chứa</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testRooms as $testRoom)
                            <tr>
                                <td>{{ $testRoom->code }}</td>
                                <td>{{ $testRoom->name }}</td>
                                <td>{{ $testRoom->testLocation->name }}</td>
                                <td>{{ $testRoom->capacity }} thí sinh</td>
                                <td>
                                    <span class="badge bg-{{ $testRoom->is_active ? 'success' : 'secondary' }}">
                                        {{ $testRoom->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('test_rooms.toggle-status', $testRoom) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-{{ $testRoom->is_active ? 'warning' : 'success' }}">
                                            {{ $testRoom->is_active ? 'Khóa' : 'Mở khóa' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('test_rooms.edit', $testRoom) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('test_rooms.destroy', $testRoom) }}" 
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
                    {{ $testRooms->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 