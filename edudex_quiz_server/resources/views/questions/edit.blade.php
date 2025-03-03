@extends('layouts.app')

@section('title', 'Chỉnh sửa câu hỏi')

@section('styles')
<link href="{{ asset('bootstrap-5.3.3/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('bootstrap-5.3.3/select2/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('questions.index') }}">Quản lý câu hỏi</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Chỉnh sửa câu hỏi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('questions.update', $question->id) }}" method="POST" id="questionForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Nội dung câu hỏi -->
                                <div class="mb-3">
                                    <label for="content" class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="3" required>{{ old('content', $question->content) }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Link media -->
                                <div class="mb-3">
                                    <label for="link_media" class="form-label">Link media</label>
                                    <input type="url" class="form-control @error('link_media') is-invalid @enderror" 
                                           id="link_media" name="link_media" value="{{ old('link_media', $question->link_media) }}"
                                           placeholder="https://minhgiang.pro/image.jpg">
                                    @error('link_media')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Các đáp án -->
                                <div class="mb-3">
                                    <label class="form-label">Đáp án <span class="text-danger">*</span></label>
                                    <div id="answers-container">
                                        @foreach($question->answers as $index => $answer)
                                        <div class="answer-group mb-3 border rounded p-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="correct_answer" 
                                                       value="{{ $index }}" {{ $answer->is_correct ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label">Là đáp án đúng</label>
                                            </div>
                                            <div class="mb-2">
                                                <input type="text" class="form-control @error('answers.'.$index.'.content') is-invalid @enderror" 
                                                       name="answers[{{ $index }}][content]" 
                                                       value="{{ old('answers.'.$index.'.content', $answer->content) }}"
                                                       placeholder="Nội dung đáp án {{ $index + 1 }}" required>
                                                @error('answers.'.$index.'.content')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div>
                                                <input type="url" class="form-control @error('answers.'.$index.'.link_media') is-invalid @enderror" 
                                                       name="answers[{{ $index }}][link_media]"
                                                       value="{{ old('answers.'.$index.'.link_media', $answer->link_media) }}"
                                                       placeholder="Link media cho đáp án {{ $index + 1 }}">
                                                @error('answers.'.$index.'.link_media')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Môn học -->
                                <div class="mb-3">
                                    <label for="subject_code" class="form-label">Môn học <span class="text-danger">*</span></label>
                                    <select class="form-select @error('subject_code') is-invalid @enderror" 
                                            id="subject_code" name="subject_code" required>
                                        <option value="">Chọn môn học</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->code }}" 
                                                {{ old('subject_code', $question->subject_code) == $subject->code ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Độ khó -->
                                <div class="mb-3">
                                    <label for="difficulty" class="form-label">Độ khó <span class="text-danger">*</span></label>
                                    <select class="form-select @error('difficulty') is-invalid @enderror" 
                                            id="difficulty" name="difficulty" required>
                                        <option value="">Chọn độ khó</option>
                                        <option value="easy" {{ old('difficulty', $question->difficulty) == 'easy' ? 'selected' : '' }}>Dễ</option>
                                        <option value="medium" {{ old('difficulty', $question->difficulty) == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                        <option value="hard" {{ old('difficulty', $question->difficulty) == 'hard' ? 'selected' : '' }}>Khó</option>
                                    </select>
                                    @error('difficulty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tags -->
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tags') is-invalid @enderror" 
                                            id="tags" name="tags[]" multiple required>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->name }}" 
                                                {{ in_array($tag->name, old('tags', $question->tags->pluck('name')->toArray())) ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tags')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Có thể nhập tag mới hoặc chọn từ danh sách có sẵn</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('questions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('bootstrap-5.3.3/select2/js/select2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Khởi tạo Select2 cho tags
    var initialTags = @json($tags->map(function($tag) {
        return [
            'id' => $tag->name,
            'text' => $tag->name,
            'newTag' => false
        ];
    }));

    $('#tags').select2({
        theme: 'bootstrap-5',
        tags: true,
        tokenSeparators: [','],
        placeholder: 'Chọn hoặc nhập tags mới...',
        allowClear: true,
        data: initialTags,
        createTag: function(params) {
            var term = $.trim(params.term);
            if (term === '') return null;

            var existingTag = initialTags.find(function(tag) {
                return tag.text.toLowerCase() === term.toLowerCase();
            });
            
            if (existingTag) return null;

            return {
                id: term,
                text: term,
                newTag: true
            }
        }
    });

    // Xử lý khi thay đổi môn học
    $('#subject_code').change(function() {
        var subjectCode = $(this).val();
        if (subjectCode) {
            $.get('/questions/tags-by-subject', { subject_code: subjectCode }, function(data) {
                var options = [];
                data.forEach(function(tag) {
                    options.push({
                        id: tag.text,
                        text: tag.text,
                        newTag: false
                    });
                });
                $('#tags').empty().select2({
                    theme: 'bootstrap-5',
                    tags: true,
                    tokenSeparators: [','],
                    placeholder: 'Chọn hoặc nhập tags mới...',
                    allowClear: true,
                    data: options,
                    createTag: function(params) {
                        var term = $.trim(params.term);
                        if (term === '') return null;

                        var existingTag = options.find(function(tag) {
                            return tag.text.toLowerCase() === term.toLowerCase();
                        });
                        
                        if (existingTag) return null;

                        return {
                            id: term,
                            text: term,
                            newTag: true
                        }
                    }
                });
            });
        } else {
            $('#tags').empty().select2({
                theme: 'bootstrap-5',
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Chọn hoặc nhập tags mới...',
                allowClear: true
            });
        }
    });
});
</script>
@endpush

@endsection 