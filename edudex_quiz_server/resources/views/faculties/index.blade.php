@extends('layouts.app')

@section('title', 'Quản lý khoa')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý khoa</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-university text-primary me-2"></i>
                            Quản lý khoa
                        </h5>
                        <div>
                            <a href="{{ route('faculties.tools') }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i> Nhập/Xuất
                            </a>
                            <a href="{{ route('faculties.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm -->
                    <form action="{{ route('faculties.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Tìm kiếm theo mã hoặc tên khoa..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Danh sách khoa -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã khoa</th>
                                    <th>Tên khoa</th>
                                    <th>Mô tả</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($faculties as $faculty)
                                <tr>
                                    <td>{{ $faculty->code }}</td>
                                    <td>{{ $faculty->name }}</td>
                                    <td>{{ $faculty->description }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('faculties.edit', $faculty->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('faculties.destroy', $faculty->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Xoá"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xoá khoa này?')">
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
                        {{ $faculties->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 