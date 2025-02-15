@extends('layouts.app')

@section('title', 'Chỉnh sửa câu hỏi - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chỉnh sửa câu hỏi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('questions.update', $question) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung câu hỏi</label>
                            <textarea name="content" class="form-control @error('content') is-invalid @enderror" 
                                      rows="3" required>{{ old('content', $question->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Môn học</label>
                            <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Chọn môn học</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" 
                                        {{ old('subject_id', $question->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Độ khó</label>
                            <select name="level" class="form-select @error('level') is-invalid @enderror" required>
                                <option value="1" {{ old('level', $question->level) == 1 ? 'selected' : '' }}>Dễ</option>
                                <option value="2" {{ old('level', $question->level) == 2 ? 'selected' : '' }}>Trung bình</option>
                                <option value="3" {{ old('level', $question->level) == 3 ? 'selected' : '' }}>Khó</option>
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <select name="tags[]" class="form-control select2-tags" multiple>
                                @foreach(App\Models\Tag::where('subject_id', old('subject_id', $question->subject_id))->get() as $tag)
                                    <option value="{{ $tag->name }}" 
                                        {{ in_array($tag->name, old('tags', $question->tags->pluck('name')->toArray())) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tags')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Bạn có thể nhập tag mới nếu chưa có sẵn</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Các đáp án</label>
                            <div id="answers-container">
                                @foreach($question->answers as $index => $answer)
                                    <div class="input-group mb-2">
                                        <input type="text" name="answers[]" 
                                               value="{{ old('answers.'.$index, $answer->content) }}"
                                               class="form-control @error('answers.'.$index) is-invalid @enderror" 
                                               placeholder="Đáp án {{ $index + 1 }}" required>
                                        <div class="input-group-text">
                                            <input type="radio" name="correct_answer" value="{{ $index }}"
                                                   {{ $answer->is_correct ? 'checked' : '' }}
                                                   required>
                                        </div>
                                        @error('answers.'.$index)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            @if($question->answers->count() < 6)
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="addAnswer()" id="add-answer-btn">
                                    Thêm đáp án
                                </button>
                            @endif
                            @error('correct_answer')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('questions.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Khởi tạo select2 cho tags
    $('.select2-tags').select2({
        tags: true,
        tokenSeparators: [',']
    });

    // Cập nhật danh sách tags khi thay đổi môn học
    $('select[name="subject_id"]').change(function() {
        const subjectId = $(this).val();
        if (subjectId) {
            $.get(`/questions/tags/${subjectId}`, function(tags) {
                const select = $('.select2-tags');
                select.empty();
                
                tags.forEach(tag => {
                    select.append(new Option(tag.name, tag.name, false, false));
                });
                
                select.trigger('change');
            });
        }
    });
});

let answerCount = {{ $question->answers->count() }};

function addAnswer() {
    if (answerCount >= 6) {
        return;
    }

    const container = document.getElementById('answers-container');
    const newAnswer = document.createElement('div');
    newAnswer.className = 'input-group mb-2';
    newAnswer.innerHTML = `
        <input type="text" name="answers[]" class="form-control" 
               placeholder="Đáp án ${answerCount + 1}" required>
        <div class="input-group-text">
            <input type="radio" name="correct_answer" value="${answerCount}" required>
        </div>
    `;
    
    container.appendChild(newAnswer);
    answerCount++;
    
    if (answerCount >= 6) {
        document.getElementById('add-answer-btn').style.display = 'none';
    }
}
</script>
@endpush
@endsection 