@extends('layouts.app')

@section('title', 'Quản lý ngành')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý ngành</li>
                </ol>
            </nav>
            @if($faculties->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một khoa trước khi thêm ngành. 
                    <a href="{{ route('faculties.create') }}" class="alert-link">Thêm khoa mới</a>
                </div>
            </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            Quản lý ngành
                        </h5>
                        <div>
                            <a href="{{ route('majors.tools') }}" class="btn btn-success {{ $faculties->isEmpty() ? 'disabled' : '' }}"
                               {{ $faculties->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-file-excel me-2"></i> Nhập/Xuất
                            </a>
                            <a href="{{ route('majors.create') }}" class="btn btn-primary {{ $faculties->isEmpty() ? 'disabled' : '' }}"
                               {{ $faculties->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-plus me-2"></i> Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form action="{{ route('majors.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Tìm kiếm theo mã hoặc tên ngành..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="faculty_id" class="form-select">
                                    <option value="">Tất cả khoa</option>
                                    @foreach($faculties as $faculty)
                                        <option value="{{ $faculty->id }}" 
                                            {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                            {{ $faculty->name }}
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

                    <!-- Danh sách ngành -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã ngành</th>
                                    <th>Tên ngành</th>
                                    <th>Khoa</th>
                                    <th>Mô tả</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($majors as $major)
                                <tr>
                                    <td>{{ $major->code }}</td>
                                    <td>{{ $major->name }}</td>
                                    <td>{{ $major->faculty->name }}</td>
                                    <td>{{ $major->description }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('majors.edit', $major->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('majors.destroy', $major->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Xoá"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xoá ngành này?')">
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
                        {{ $majors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 