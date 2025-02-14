@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý tài khoản</h4>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Thêm mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('users.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm kiếm theo username">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">Tất cả vai trò</option>
                        <option value="0" {{ request('role') === '0' ? 'selected' : '' }}>Quản trị viên</option>
                        <option value="1" {{ request('role') === '1' ? 'selected' : '' }}>Giáo viên</option>
                        <option value="2" {{ request('role') === '2' ? 'selected' : '' }}>Nhân viên</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($users->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('role'))
                        <a href="{{ route('users.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->userInfo->fullName }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 0)
                                        <span class="badge bg-primary">Quản trị viên</span>
                                    @elseif ($user->role === 1)
                                        <span class="badge bg-success">Giáo viên</span>
                                    @else
                                        <span class="badge bg-secondary">Nhân viên</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                          class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
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
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
