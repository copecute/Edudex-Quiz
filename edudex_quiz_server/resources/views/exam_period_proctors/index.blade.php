@extends('layouts.app')

@section('title', 'Quản lý cán bộ coi thi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-periods.index') }}">Kỳ thi</a></li>
                    <li class="breadcrumb-item active">Cán bộ coi thi - {{ $examPeriod->name }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách cán bộ coi thi</h5>
                    <div>
                        <a href="{{ route('exam-period-proctors.tools', $examPeriod) }}" class="btn btn-success me-2">
                            <i class="fas fa-file-excel me-1"></i> Nhập/Xuất
                        </a>
                        <a href="{{ route('exam-period-proctors.assign', $examPeriod) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Phân công
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('exam-period-proctors.index', $examPeriod) }}" method="GET" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Tìm kiếm theo tên, username, số điện thoại..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Họ và tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Vai trò</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proctors as $proctor)
                                <tr>
                                    <td>{{ $proctor->username }}</td>
                                    <td>{{ $proctor->fullName }}</td>
                                    <td>{{ $proctor->email }}</td>
                                    <td>{{ $proctor->phoneNumber }}</td>
                                    <td>
                                        @if($proctor->role == 0)
                                            <span class="badge bg-info">Cán bộ</span>
                                        @elseif($proctor->role == 1)
                                            <span class="badge bg-primary">Giáo viên</span>
                                        @elseif($proctor->role == 2)
                                            <span class="badge bg-success">Quản trị viên</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('exam-period-proctors.destroy', ['examPeriod' => $examPeriod->id, 'proctor' => $proctor->id]) }}" 
                                              method="POST" 
                                              class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $proctors->links() }}
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
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn xóa?')) {
            this.submit();
        }
    });
});
</script>
@endpush 