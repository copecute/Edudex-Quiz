@extends('layouts.app')

@section('title', 'Quản lý đề thi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Quản lý đề thi</h4>
        </div>
        <a href="{{ route('test_papers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo đề thi mới
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Form tìm kiếm -->
            <form action="{{ route('test_papers.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo tên đề thi">
                </div>
                <div class="col-md-3">
                    <select name="subject_id" class="form-select">
                        <option value="">Tất cả môn học</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" 
                                {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($testPapers->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy đề thi nào</p>
                    @if(request('search') || request('subject_id'))
                        <a href="{{ route('test_papers.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên đề thi</th>
                                <th>Môn học</th>
                                <th>Thời gian (phút)</th>
                                <th>Số câu hỏi</th>
                                <th>Độ khó</th>
                                <th>Tags</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testPapers as $testPaper)
                            <tr>
                                <td>
                                    {{ $testPaper->name }}
                                    @if($testPaper->description)
                                        <i class="fas fa-info-circle text-info" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $testPaper->description }}"></i>
                                    @endif
                                </td>
                                <td>{{ $testPaper->subject->name }}</td>
                                <td>{{ $testPaper->duration }}</td>
                                <td>{{ $testPaper->total_questions }}</td>
                                <td>
                                    @php $rates = $testPaper->difficulty_rates; @endphp
                                    <div class="small">
                                        <div>Dễ: {{ number_format($rates['easy'], 1) }}%</div>
                                        <div>TB: {{ number_format($rates['medium'], 1) }}%</div>
                                        <div>Khó: {{ number_format($rates['hard'], 1) }}%</div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($testPaper->tags as $tag)
                                        <div class="small">
                                            {{ $tag->name }}: {{ $tag->pivot->num_questions }} câu
                                            <span class="text-muted">
                                                ({{ $tag->pivot->easy_rate }}/{{ $tag->pivot->medium_rate }}/{{ $tag->pivot->hard_rate }})
                                            </span>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('test_papers.edit', $testPaper) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('test_papers.destroy', $testPaper) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $testPapers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
@endsection 