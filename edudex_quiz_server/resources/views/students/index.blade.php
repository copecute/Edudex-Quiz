@extends('layouts.app')

@section('title', 'Quản lý thí sinh - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Quản lý thí sinh</h2>
        <a href="{{ route('students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm thí sinh
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('students.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Tìm kiếm..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Trạng thái</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đang học</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Đã nghỉ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="gender" class="form-select" onchange="this.form.submit()">
                            <option value="">Giới tính</option>
                            <option value="1" {{ request('gender') == '1' ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ request('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="major_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Ngành học</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->id }}" 
                                    {{ request('major_id') == $major->id ? 'selected' : '' }}>
                                    {{ $major->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        @if(request()->hasAny(['search', 'status', 'gender', 'major_id']))
                            <a href="{{ route('students.index') }}" class="btn btn-light">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mã SV</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Ngành học</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>{{ $student->code }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    @foreach ($student->majors as $major)
                                        <span class="badge bg-{{ $major->pivot->is_main ? 'primary' : 'secondary' }} me-1">
                                            {{ $major->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-{{ $student->status ? 'success' : 'danger' }}">
                                        {{ $student->status ? 'Đang học' : 'Đã nghỉ' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('students.edit', $student) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('students.destroy', $student) }}" 
                                          method="POST" 
                                          class="d-inline-block"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
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
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 