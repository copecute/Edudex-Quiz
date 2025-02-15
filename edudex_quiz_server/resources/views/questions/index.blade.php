@extends('layouts.app')

@section('title', 'Quản lý câu hỏi - Edudex Quiz')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Quản lý câu hỏi</h4>
        <a href="{{ route('questions.create') }}" class="btn btn-primary">Thêm câu hỏi mới</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tìm kiếm và lọc -->
            <form action="{{ route('questions.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Tìm theo nội dung câu hỏi">
                </div>
                <div class="col-md-2">
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
                    <select name="level" class="form-select">
                        <option value="">Tất cả độ khó</option>
                        <option value="1" {{ request('level') == 1 ? 'selected' : '' }}>Dễ</option>
                        <option value="2" {{ request('level') == 2 ? 'selected' : '' }}>Trung bình</option>
                        <option value="3" {{ request('level') == 3 ? 'selected' : '' }}>Khó</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tag_id" class="form-select">
                        <option value="">Tất cả tags</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" 
                                {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>

            @if($questions->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">Không tìm thấy kết quả nào</p>
                    @if(request('search') || request('subject_id') || request('level') || request('tag_id'))
                        <a href="{{ route('questions.index') }}" class="btn btn-link">Xóa bộ lọc</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nội dung</th>
                                <th>Môn học</th>
                                <th>Độ khó</th>
                                <th>Tags</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questions as $question)
                            <tr>
                                <td>
                                    {{ Str::limit($question->content, 100) }}
                                    @if(strlen($question->content) > 100)
                                        <a href="#" data-bs-toggle="modal" 
                                           data-bs-target="#questionModal{{ $question->id }}">
                                            Xem thêm
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $question->subject->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $question->level == 1 ? 'success' : ($question->level == 2 ? 'warning' : 'danger') }}">
                                        {{ $question->getLevelText() }}
                                    </span>
                                </td>
                                <td>
                                    @foreach($question->tags as $tag)
                                        <span class="badge bg-secondary">{{ $tag->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('questions.edit', $question) }}" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('questions.destroy', $question) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal hiển thị chi tiết câu hỏi -->
                            <div class="modal fade" id="questionModal{{ $question->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chi tiết câu hỏi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6>Câu hỏi:</h6>
                                            <p>{{ $question->content }}</p>
                                            
                                            <h6>Các đáp án:</h6>
                                            <ul class="list-group">
                                                @foreach($question->answers as $answer)
                                                    <li class="list-group-item {{ $answer->is_correct ? 'list-group-item-success' : '' }}">
                                                        {{ $answer->content }}
                                                        @if($answer->is_correct)
                                                            <span class="badge bg-success float-end">Đáp án đúng</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $questions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 