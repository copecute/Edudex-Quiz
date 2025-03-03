@extends('layouts.app')

@section('title', 'Quản lý tài khoản')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý tài khoản</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users text-primary me-2"></i>
                            Quản lý tài khoản
                        </h5>
                        <div>
                            <a href="{{ route('accounts.tools') }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i> Nhập/Xuất
                            </a>
                            <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form action="{{ route('accounts.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Tìm kiếm..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-select">
                                    <option value="">Tất cả vai trò</option>
                                    <option value="0" {{ request('role') === '0' ? 'selected' : '' }}>Cán bộ coi thi</option>
                                    <option value="1" {{ request('role') === '1' ? 'selected' : '' }}>Giáo viên</option>
                                    <option value="2" {{ request('role') === '2' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Danh sách tài khoản -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Họ tên</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>{{ $account->username }}</td>
                                    <td>{{ $account->email }}</td>
                                    <td>{{ $account->accountInfo->fullName }}</td>
                                    <td>
                                        @switch($account->role)
                                            @case(0)
                                                <span class="badge bg-info">Cán bộ coi thi</span>
                                                @break
                                            @case(1)
                                                <span class="badge bg-success">Giáo viên</span>
                                                @break
                                            @case(2)
                                                <span class="badge bg-danger">Admin</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $account->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $account->is_active ? 'Hoạt động' : 'Đã khoá' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">

                                            <!-- Nút sửa -->
                                            <a href="{{ route('accounts.edit', $account->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Nút khoá/mở khoá -->
                                            @if(auth()->user()->role === 2)
                                                <form action="{{ route('accounts.toggle-status', $account->id) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" 
                                                            class="btn btn-sm {{ $account->is_active ? 'btn-danger' : 'btn-success' }}"
                                                            title="{{ $account->is_active ? 'Khoá tài khoản' : 'Mở khoá tài khoản' }}"
                                                            {{ $account->id === auth()->id() ? 'disabled' : '' }}
                                                            onclick="return confirm('{{ $account->is_active ? 'Bạn có chắc chắn muốn khoá tài khoản này?' : 'Bạn có chắc chắn muốn mở khoá tài khoản này?' }}')">
                                                        <i class="fas {{ $account->is_active ? 'fa-lock' : 'fa-unlock' }}"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Nút xoá -->
                                            <form action="{{ route('accounts.destroy', $account->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Xoá"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xoá tài khoản này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $accounts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 