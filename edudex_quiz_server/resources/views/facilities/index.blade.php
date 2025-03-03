@extends('layouts.app')

@section('title', 'Quản lý cơ sở thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý cơ sở thi</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-building text-primary me-2"></i>
                            Danh sách cơ sở thi
                        </h5>
                        <div>
                            <a href="{{ route('facilities.tools') }}" class="btn btn-success me-2">
                                <i class="fas fa-file-excel me-2"></i>Nhập/Xuất
                            </a>
                            <a href="{{ route('facilities.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form method="GET" action="{{ route('facilities.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Tìm kiếm theo mã, tên hoặc địa chỉ..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đã khóa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
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
                                    <th>Mã cơ sở</th>
                                    <th>Tên cơ sở</th>
                                    <th>Địa chỉ</th>
                                    <th>Số phòng thi</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facilities as $facility)
                                <tr>
                                    <td>{{ $facility->code }}</td>
                                    <td>{{ $facility->name }}</td>
                                    <td>{{ $facility->address }}</td>
                                    <td>{{ $facility->rooms->count() }}</td>
                                    <td>
                                        @if($facility->is_active)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-danger">Đã khóa</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('facilities.destroy', $facility) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            
                                            <a href="{{ route('facilities.edit', $facility) }}" 
                                               class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button type="button" 
                                                    class="btn btn-sm btn-{{ $facility->is_active ? 'warning' : 'success' }}"
                                                    onclick="toggleStatus('{{ route('facilities.toggle-status', $facility) }}')"
                                                    title="{{ $facility->is_active ? 'Khóa' : 'Mở khóa' }}">
                                                <i class="fas fa-{{ $facility->is_active ? 'lock' : 'unlock' }}"></i>
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
                            Hiển thị {{ $facilities->firstItem() ?? 0 }}-{{ $facilities->lastItem() ?? 0 }} 
                            trên tổng số {{ $facilities->total() ?? 0 }} kết quả
                        </div>
                        {{ $facilities->links() }}
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
                  if (data.success) {
                      window.location.href = '{{ route("facilities.index") }}?success=' + encodeURIComponent(data.message);
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
            window.history.replaceState({}, '', '{{ route("facilities.index") }}');
            
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