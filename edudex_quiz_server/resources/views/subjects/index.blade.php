@extends('layouts.app')

@section('title', 'Quản lý môn học')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý môn học</li>
                </ol>
            </nav>

            @if($majors->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một ngành trước khi thêm môn học. 
                    <a href="{{ route('majors.create') }}" class="alert-link">Thêm ngành mới</a>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-book text-primary me-2"></i>
                            Quản lý môn học
                        </h5>
                        <div>
                            <a href="{{ route('subjects.tools') }}" class="btn btn-success {{ $majors->isEmpty() ? 'disabled' : '' }}"
                               {{ $majors->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-file-excel me-2"></i> Nhập/Xuất
                            </a>
                            <a href="{{ route('subjects.create') }}" class="btn btn-primary {{ $majors->isEmpty() ? 'disabled' : '' }}"
                               {{ $majors->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-plus me-2"></i> Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form action="{{ route('subjects.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Tìm kiếm theo mã hoặc tên môn học..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="major_id" class="form-select">
                                    <option value="">Tất cả ngành</option>
                                    @foreach($majors as $major)
                                        <option value="{{ $major->id }}" 
                                            {{ request('major_id') == $major->id ? 'selected' : '' }}>
                                            {{ $major->name }} ({{ $major->faculty->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Danh sách môn học -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã môn học</th>
                                    <th>Tên môn học</th>
                                    <th>Số tín chỉ</th>
                                    <th>Ngành</th>
                                    <th>Mô tả</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->code }}</td>
                                    <td>{{ $subject->name }}</td>
                                    <td>{{ $subject->credits }}</td>
                                    <td>{{ $subject->major->name }}</td>
                                    <td>{{ $subject->description }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('subjects.edit', $subject->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('subjects.destroy', $subject->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Xoá"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xoá môn học này?')">
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
                        {{ $subjects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 