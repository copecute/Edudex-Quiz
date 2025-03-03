@extends('layouts.app')

@section('title', 'Quản lý câu hỏi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Quản lý câu hỏi</li>
                </ol>
            </nav>

            @if($subjects->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Bạn cần thêm ít nhất một môn học trước khi thêm câu hỏi. 
                    <a href="{{ route('subjects.create') }}" class="alert-link">Thêm môn học mới</a>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            Quản lý câu hỏi
                        </h5>
                        <div>
                            <a href="{{ route('questions.tools') }}" class="btn btn-success {{ $subjects->isEmpty() ? 'disabled' : '' }}"
                               {{ $subjects->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-file-excel me-2"></i> Nhập/Xuất
                            </a>
                            <a href="{{ route('questions.create') }}" class="btn btn-primary {{ $subjects->isEmpty() ? 'disabled' : '' }}"
                               {{ $subjects->isEmpty() ? 'aria-disabled=true tabindex=-1' : '' }}>
                                <i class="fas fa-plus me-2"></i> Thêm mới
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tìm kiếm và lọc -->
                    <form action="{{ route('questions.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Tìm kiếm theo nội dung..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="subject_code" class="form-select" id="subject_select">
                                    <option value="">Tất cả môn học</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->code }}" 
                                            {{ request('subject_code') == $subject->code ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="difficulty" class="form-select">
                                    <option value="">Tất cả độ khó</option>
                                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Dễ</option>
                                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Khó</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="tag_id" class="form-select" id="tag_select">
                                    <option value="">Tất cả tags</option>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}" 
                                            {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Danh sách câu hỏi -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nội dung</th>
                                    <th>Môn học</th>
                                    <th>Độ khó</th>
                                    <th>Tags</th>
                                    <th>Số đáp án</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $question)
                                <tr>
                                    <td>
                                        {{ Str::limit($question->content, 50) }}
                                        @if($question->link_media)
                                            <i class="fas fa-image text-info ms-1" title="Có hình ảnh"></i>
                                        @endif
                                    </td>
                                    <td>{{ $question->subject->name }}</td>
                                    <td>
                                        @switch($question->difficulty)
                                            @case('easy')
                                                <span class="badge bg-success">Dễ</span>
                                                @break
                                            @case('medium')
                                                <span class="badge bg-warning">Trung bình</span>
                                                @break
                                            @case('hard')
                                                <span class="badge bg-danger">Khó</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @foreach($question->tags as $tag)
                                            <span class="badge bg-info">{{ $tag->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $question->answers->count() }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('questions.edit', $question->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('questions.destroy', $question->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Xoá"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xoá câu hỏi này?')">
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
                        {{ $questions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Xử lý khi thay đổi môn học
    $('#subject_select').change(function() {
        var subjectCode = $(this).val();
        if (subjectCode) {
            // Gọi API lấy tags của môn học
            $.get('/questions/tags-by-subject', { subject_code: subjectCode }, function(data) {
                var options = '<option value="">Tất cả tags</option>';
                data.forEach(function(tag) {
                    options += `<option value="${tag.id}">${tag.text}</option>`;
                });
                $('#tag_select').html(options);
            });
        } else {
            $('#tag_select').html('<option value="">Tất cả tags</option>');
        }
    });
});
</script>
@endpush

@endsection 