@extends('layouts.app')

@section('title', 'Quản lý phòng thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý phòng thi</li>
                </ol>
            </nav>

            @if($facilities->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một cơ sở thi trước khi thêm phòng thi. 
                    <a href="{{ route('facilities.create') }}" class="alert-link">Thêm cơ sở thi mới</a>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-door-open text-primary me-2"></i>
                            Danh sách phòng thi
                        </h5>
                        <div>
                            <a href="{{ route('rooms.tools') }}" class="btn btn-success me-2 {{ $facilities->isEmpty() ? 'disabled' : '' }}"
                               {{ $facilities->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-file-excel me-2"></i>Nhập/Xuất
                            </a>
                            <a href="{{ route('rooms.create') }}" class="btn btn-primary {{ $facilities->isEmpty() ? 'disabled' : '' }}"
                               {{ $facilities->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-plus me-2"></i>Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form method="GET" action="{{ route('rooms.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Tìm kiếm theo mã, tên..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="facility_id" class="form-select">
                                    <option value="">Tất cả cơ sở</option>
                                    @foreach($facilities as $facility)
                                        <option value="{{ $facility->id }}" 
                                            {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                            {{ $facility->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đã khóa</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Danh sách -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã phòng thi</th>
                                    <th>Tên phòng thi</th>
                                    <th>Cơ sở</th>
                                    <th>Sức chứa</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rooms as $room)
                                <tr>
                                    <td>{{ $room->code }}</td>
                                    <td>{{ $room->name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $room->facility->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $room->capacity }} thí sinh
                                        </span>
                                    </td>
                                    <td>
                                        @if($room->is_active)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-danger">Đã khóa</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('rooms.destroy', $room) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            
                                            <a href="{{ route('rooms.edit', $room) }}" 
                                               class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-{{ $room->is_active ? 'warning' : 'success' }}"
                                                    onclick="toggleStatus('{{ route('rooms.toggle-status', $room) }}')"
                                                    title="{{ $room->is_active ? 'Khóa' : 'Mở khóa' }}">
                                                <i class="fas fa-{{ $room->is_active ? 'lock' : 'unlock' }}"></i>
                                            </button>

                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa?')"
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                        Không có dữ liệu
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Hiển thị {{ $rooms->firstItem() ?? 0 }}-{{ $rooms->lastItem() ?? 0 }} 
                            trên tổng số {{ $rooms->total() ?? 0 }} kết quả
                        </div>
                        {{ $rooms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleStatus(url) {
        if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái?')) {
            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      window.location.href = '{{ route("rooms.index") }}?success=' + encodeURIComponent(data.message);
                  } else {
                      throw new Error(data.message);
                  }
              }).catch(error => {
                  console.error('Error:', error);
                  alert('Có lỗi xảy ra khi thay đổi trạng thái');
              });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const successMessage = urlParams.get('success');
        if (successMessage) {
            window.history.replaceState({}, '', '{{ route("rooms.index") }}');
            
            var toastEl = document.createElement('div');
            toastEl.className = 'toast align-items-center text-white bg-success border-0';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>${successMessage}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            document.getElementById('toast-container').appendChild(toastEl);
            var toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 3000
            });
            toast.show();
        }
    });
</script>
@endpush
@endsection 