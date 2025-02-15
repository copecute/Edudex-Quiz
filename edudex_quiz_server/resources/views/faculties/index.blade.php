@extends('layouts.app')

@section('title', 'Quản lý khoa - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý khoa</h4>
        <a href="{{ route('faculties.create') }}" class="btn btn-primary">Thêm khoa mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm -->
            <form action="{{ route('faculties.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo mã hoặc tên khoa">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($faculties->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search'))
                        <a href="{{ route('faculties.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã khoa</th>
                                <th>Tên khoa</th>
                                <th>Mô tả</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($faculties as $faculty)
                            <tr>
                                <td>{{ $faculty->code }}</td>
                                <td>
                                    <a href="{{ route('majors.index', ['faculty_id' => $faculty->id]) }}" 
                                       class="text-decoration-none">
                                        {{ $faculty->name }}
                                        <span class="badge bg-secondary ms-1">
                                            {{ $faculty->majors->count() }} ngành
                                        </span>
                                    </a>
                                </td>
                                <td>{{ $faculty->description }}</td>
                                <td>
                                    <a href="{{ route('faculties.edit', $faculty) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('faculties.destroy', $faculty) }}" 
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
                    {{ $faculties->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 