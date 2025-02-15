@extends('layouts.app')

@section('title', 'Quản lý ngành - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý ngành</h4>
        <a href="{{ route('majors.create') }}" class="btn btn-primary">Thêm ngành mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('majors.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo mã hoặc tên ngành">
                </div>
                <div class="col-md-3">
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
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($majors->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('faculty_id'))
                        <a href="{{ route('majors.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã ngành</th>
                                <th>Tên ngành</th>
                                <th>Khoa</th>
                                <th>Mô tả</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($majors as $major)
                            <tr>
                                <td>{{ $major->code }}</td>
                                <td>{{ $major->name }}</td>
                                <td>{{ $major->faculty->name }}</td>
                                <td>{{ $major->description }}</td>
                                <td>
                                    <a href="{{ route('majors.edit', $major) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('majors.destroy', $major) }}" 
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
                    {{ $majors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 