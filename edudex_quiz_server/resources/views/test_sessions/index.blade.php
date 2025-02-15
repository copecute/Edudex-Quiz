@extends('layouts.app')

@section('title', 'Quản lý kỳ thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý kỳ thi</h4>
        <a href="{{ route('test_sessions.create') }}" class="btn btn-primary">Tạo kỳ thi mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('test_sessions.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo tên kỳ thi">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>
                            Chưa bắt đầu
                        </option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>
                            Đang diễn ra
                        </option>
                        <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>
                            Đã kết thúc
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

            @if($testSessions->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('status'))
                        <a href="{{ route('test_sessions.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên kỳ thi</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testSessions as $testSession)
                            <tr>
                                <td>
                                    {{ $testSession->name }}
                                    @if($testSession->description)
                                        <i class="fas fa-info-circle text-info" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $testSession->description }}"></i>
                                    @endif
                                </td>
                                <td>
                                    <div>Bắt đầu: {{ $testSession->start_time->format('d/m/Y H:i') }}</div>
                                    <div>Kết thúc: {{ $testSession->end_time->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $testSession->getStatusColor() }}">
                                        {{ $testSession->getStatusText() }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('test_sessions.subjects.index', $testSession) }}" 
                                       class="btn btn-sm btn-info">Môn thi</a>
                                    <a href="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                                       class="btn btn-sm btn-info">Ca thi</a>
                                    <form action="{{ route('test_sessions.toggle-status', $testSession) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-{{ $testSession->is_active ? 'warning' : 'success' }}">
                                            {{ $testSession->is_active ? 'Khóa' : 'Mở khóa' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('test_sessions.edit', $testSession) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('test_sessions.destroy', $testSession) }}" 
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
                    {{ $testSessions->links() }}
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