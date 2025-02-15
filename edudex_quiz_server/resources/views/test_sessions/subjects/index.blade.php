@extends('layouts.app')

@section('title', 'Quản lý môn thi - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý môn thi</h4>
            <p class="text-muted mb-0">Kỳ thi: {{ $testSession->name }}</p>
        </div>
        <a href="{{ route('test_sessions.index') }}" class="btn btn-light">Quay lại</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thêm môn thi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('test_sessions.subjects.store', $testSession) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Chọn môn thi</label>
                            <select name="subjects[]" class="form-select select2-multiple" multiple required>
                                @foreach($availableSubjects as $subject)
                                    <option value="{{ $subject->id }}">
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm môn thi</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Danh sách môn thi</h5>
                </div>
                <div class="card-body">
                    @if($subjects->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Chưa có môn thi nào</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã môn</th>
                                        <th>Tên môn</th>
                                        <th>Số ca thi</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $subject)
                                    <tr>
                                        <td>{{ $subject->code }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->test_shifts_count }}</td>
                                        <td>
                                            <a href="{{ route('test_sessions.test_shifts.index', [$testSession, 'subject' => $subject->id]) }}" 
                                               class="btn btn-sm btn-info">Ca thi</a>
                                            <form action="{{ route('test_sessions.subjects.destroy', [$testSession, $subject]) }}" 
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
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Chọn môn thi',
        allowClear: true
    });
});
</script>
@endpush 