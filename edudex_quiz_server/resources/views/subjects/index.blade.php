@extends('layouts.app')

@section('title', 'Quản lý môn học - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý môn học</h4>
        <a href="{{ route('subjects.create') }}" class="btn btn-primary">Thêm môn học mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('subjects.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo mã hoặc tên môn học">
                </div>
                <div class="col-md-3">
                    <select name="faculty_id" class="form-select" id="faculty-select">
                        <option value="">Tất cả khoa</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}" 
                                {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                {{ $faculty->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="major_id" class="form-select" id="major-select">
                        <option value="">Tất cả ngành</option>
                        @foreach($majors as $major)
                            <option value="{{ $major->id }}" 
                                data-faculty="{{ $major->faculty_id }}"
                                {{ request('major_id') == $major->id ? 'selected' : '' }}>
                                {{ $major->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($subjects->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('faculty_id') || request('major_id'))
                        <a href="{{ route('subjects.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã môn học</th>
                                <th>Tên môn học</th>
                                <th>Số tín chỉ</th>
                                <th>Khoa</th>
                                <th>Ngành</th>
                                <th>Mô tả</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $subject)
                            <tr>
                                <td>{{ $subject->code }}</td>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->credits }}</td>
                                <td>{{ $subject->major->faculty->name }}</td>
                                <td>{{ $subject->major->name }}</td>
                                <td>{{ $subject->description }}</td>
                                <td>
                                    <a href="{{ route('subjects.edit', $subject) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('subjects.destroy', $subject) }}" 
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
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const facultySelect = document.getElementById('faculty-select');
    const majorSelect = document.getElementById('major-select');
    const majorOptions = Array.from(majorSelect.options);

    facultySelect.addEventListener('change', function() {
        const selectedFacultyId = this.value;
        
        // Reset major select
        majorSelect.value = '';
        
        // Show/hide major options based on selected faculty
        majorOptions.forEach(option => {
            if (!selectedFacultyId || option.dataset.faculty === selectedFacultyId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Trigger change event on page load if faculty is selected
    if (facultySelect.value) {
        facultySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection 