@extends('layouts.app')

@section('title', 'Quản lý ca thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý ca thi</h4>
            <p class="text-muted mb-0">Kỳ thi: {{ $testSession->name }}</p>
        </div>
        <div>
            <a href="{{ route('test_sessions.index') }}" class="btn btn-light me-2">Quay lại</a>
            <a href="{{ route('test_sessions.test_shifts.create', $testSession) }}" 
               class="btn btn-primary">Thêm ca thi mới</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                  method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo tên ca thi">
                </div>
                <div class="col-md-3">
                    <select name="subject" class="form-select">
                        <option value="">Tất cả môn thi</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" 
                                {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
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

            @if($testShifts->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('status') || request('subject'))
                        <a href="{{ route('test_sessions.test_shifts.index', $testSession) }}" 
                           class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên ca thi</th>
                                <th>Môn thi</th>
                                <th>Ngày thi</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testShifts as $testShift)
                            <tr>
                                <td>
                                    {{ $testShift->name }}
                                    @if($testShift->description)
                                        <i class="fas fa-info-circle text-info" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $testShift->description }}"></i>
                                    @endif
                                </td>
                                <td>
                                    {{ $testShift->subject_names }}
                                </td>
                                <td>{{ $testShift->start_time->format('d/m/Y') }}</td>
                                <td>{{ $testShift->start_time->format('H:i') }} - {{ $testShift->end_time->format('H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $testShift->is_active ? 'success' : 'secondary' }}">
                                        {{ $testShift->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('test_sessions.test_shifts.toggle-status', [$testSession, $testShift]) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-{{ $testShift->is_active ? 'warning' : 'success' }}">
                                            {{ $testShift->is_active ? 'Khóa' : 'Mở khóa' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('test_sessions.test_shifts.test_rooms.index', [$testSession, $testShift]) }}" 
                                       class="btn btn-sm btn-info">Phòng thi</a>
                                    <a href="{{ route('test_sessions.test_shifts.edit', [$testSession, $testShift]) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('test_sessions.test_shifts.destroy', [$testSession, $testShift]) }}" 
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
                    {{ $testShifts->links() }}
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