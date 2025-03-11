@extends('layouts.app')

@section('title', 'Chỉnh sửa đề thi')

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
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Quản lý đề thi</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Chỉnh sửa đề thi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger tag-questions-error" style="display: none;"></div>
                    <form action="{{ route('exams.update', $exam) }}" method="POST" id="examForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Thông tin cơ bản -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tên đề thi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           name="name" value="{{ old('name', $exam->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Môn học <span class="text-danger">*</span></label>
                                    <select class="form-select @error('subject_code') is-invalid @enderror" 
                                            name="subject_code" id="subject_select" required>
                                        <option value="">Chọn môn học...</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->code }}" 
                                                {{ old('subject_code', $exam->subject_code) == $subject->code ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian làm bài (phút) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                           name="duration" value="{{ old('duration', $exam->duration) }}" min="1" required>
                                    <small class="text-warning duration-warning" style="display: none;"></small>
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tổng số câu hỏi <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('total_questions') is-invalid @enderror" 
                                           name="total_questions" id="total_questions" 
                                           value="{{ old('total_questions', $exam->total_questions) }}" min="1" required>
                                    @error('total_questions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              name="description" rows="3">{{ old('description', $exam->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tỷ lệ độ khó chung -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Tỷ lệ độ khó chung (tự động tính nếu có tags)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Tỷ lệ dễ (%)</label>
                                        <input type="number" class="form-control difficulty-rate" name="easy_rate" 
                                               value="{{ old('easy_rate', $exam->easy_rate) }}" min="0" max="100" required readonly>
                                        <small class="text-muted easy-questions"></small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tỷ lệ trung bình (%)</label>
                                        <input type="number" class="form-control difficulty-rate" name="medium_rate" 
                                               value="{{ old('medium_rate', $exam->medium_rate) }}" min="0" max="100" required readonly>
                                        <small class="text-muted medium-questions"></small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tỷ lệ khó (%)</label>
                                        <input type="number" class="form-control difficulty-rate" name="hard_rate" 
                                               value="{{ old('hard_rate', $exam->hard_rate) }}" min="0" max="100" required readonly>
                                        <small class="text-muted hard-questions"></small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-danger difficulty-rate-error mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Phân bổ câu hỏi theo tags</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="addTagBtn">
                                    <i class="fas fa-plus me-2"></i>Thêm tag
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="tagContainer">
                                    @foreach($exam->examTags as $index => $examTag)
                                    <div class="tag-item border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Tag</label>
                                                <select class="form-select tag-select" name="tags[{{ $index }}][id]" required>
                                                    <option value="{{ $examTag->tag_id }}" selected>
                                                        {{ $examTag->tag->name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Số câu hỏi</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control tag-num-questions" 
                                                           name="tags[{{ $index }}][num_questions]" 
                                                           value="{{ $examTag->num_questions }}" min="1" required>
                                                    <button type="button" class="btn btn-outline-danger remove-tag">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Dễ (%)</label>
                                                <input type="number" class="form-control tag-difficulty-rate" 
                                                       name="tags[{{ $index }}][easy_rate]" 
                                                       value="{{ $examTag->easy_rate }}" min="0" max="100" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Trung bình (%)</label>
                                                <input type="number" class="form-control tag-difficulty-rate" 
                                                       name="tags[{{ $index }}][medium_rate]" 
                                                       value="{{ $examTag->medium_rate }}" min="0" max="100" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Khó (%)</label>
                                                <input type="number" class="form-control tag-difficulty-rate" 
                                                       name="tags[{{ $index }}][hard_rate]" 
                                                       value="{{ $examTag->hard_rate }}" min="0" max="100" required>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @error('tags')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('exams.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template for tag item -->
<template id="tagItemTemplate">
    <div class="tag-item border rounded p-3 mb-3">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tag</label>
                <select class="form-select tag-select" name="tags[INDEX][id]" required>
                    <option value="">Chọn tag...</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Số câu hỏi</label>
                <div class="input-group">
                    <input type="number" class="form-control tag-num-questions" 
                           name="tags[INDEX][num_questions]" min="1" required>
                    <button type="button" class="btn btn-outline-danger remove-tag">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Dễ (%)</label>
                <input type="number" class="form-control tag-difficulty-rate" 
                       name="tags[INDEX][easy_rate]" value="40" min="0" max="100" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Trung bình (%)</label>
                <input type="number" class="form-control tag-difficulty-rate" 
                       name="tags[INDEX][medium_rate]" value="40" min="0" max="100" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Khó (%)</label>
                <input type="number" class="form-control tag-difficulty-rate" 
                       name="tags[INDEX][hard_rate]" value="20" min="0" max="100" required>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="{{ asset('bootstrap-5.3.3/select2/js/select2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Cập nhật số câu hỏi theo độ khó
    function updateQuestionCounts() {
        const totalQuestions = parseInt($('#total_questions').val()) || 0;
        const easyRate = parseInt($('input[name="easy_rate"]').val()) || 0;
        const mediumRate = parseInt($('input[name="medium_rate"]').val()) || 0;
        const hardRate = parseInt($('input[name="hard_rate"]').val()) || 0;

        // Tính tổng số câu cho mỗi độ khó
        const totalEasy = Math.round(totalQuestions * easyRate / 100);
        const totalMedium = Math.round(totalQuestions * mediumRate / 100);
        const totalHard = Math.round(totalQuestions * hardRate / 100);

        // Tính số câu từ các tag
        let tagEasy = 0;
        let tagMedium = 0;
        let tagHard = 0;
        $('.tag-item').each(function() {
            const numQuestions = parseInt($(this).find('input[name*="num_questions"]').val()) || 0;
            const easyRate = parseInt($(this).find('input[name*="easy_rate"]').val()) || 0;
            const mediumRate = parseInt($(this).find('input[name*="medium_rate"]').val()) || 0;
            const hardRate = parseInt($(this).find('input[name*="hard_rate"]').val()) || 0;
            
            tagEasy += Math.round(numQuestions * easyRate / 100);
            tagMedium += Math.round(numQuestions * mediumRate / 100);
            tagHard += Math.round(numQuestions * hardRate / 100);
        });

        // Tính số câu ngẫu nhiên (còn lại)
        const randomEasy = totalEasy - tagEasy;
        const randomMedium = totalMedium - tagMedium;
        const randomHard = totalHard - tagHard;

        // Hiển thị chi tiết
        let easyDetail = `(${totalEasy} câu`;
        if (tagEasy > 0) {
            const tagDetails = [];
            $('.tag-item').each(function() {
                const tagName = $(this).find('.tag-select option:selected').text() || 'Tag';
                const numQuestions = parseInt($(this).find('input[name*="num_questions"]').val()) || 0;
                const easyRate = parseInt($(this).find('input[name*="easy_rate"]').val()) || 0;
                const easyCount = Math.round(numQuestions * easyRate / 100);
                if (easyCount > 0) {
                    tagDetails.push(`${tagName}: ${easyCount} câu`);
                }
            });
            easyDetail += ` - ${tagDetails.join(', ')}`;
            if (randomEasy > 0) {
                easyDetail += `, ${randomEasy} câu ngẫu nhiên`;
            }
        }
        easyDetail += ')';

        // Tương tự cho medium và hard
        let mediumDetail = `(${totalMedium} câu`;
        if (tagMedium > 0) {
            const tagDetails = [];
            $('.tag-item').each(function() {
                const tagName = $(this).find('.tag-select option:selected').text() || 'Tag';
                const numQuestions = parseInt($(this).find('input[name*="num_questions"]').val()) || 0;
                const mediumRate = parseInt($(this).find('input[name*="medium_rate"]').val()) || 0;
                const mediumCount = Math.round(numQuestions * mediumRate / 100);
                if (mediumCount > 0) {
                    tagDetails.push(`${tagName}: ${mediumCount} câu`);
                }
            });
            mediumDetail += ` - ${tagDetails.join(', ')}`;
            if (randomMedium > 0) {
                mediumDetail += `, ${randomMedium} câu ngẫu nhiên`;
            }
        }
        mediumDetail += ')';

        let hardDetail = `(${totalHard} câu`;
        if (tagHard > 0) {
            const tagDetails = [];
            $('.tag-item').each(function() {
                const tagName = $(this).find('.tag-select option:selected').text() || 'Tag';
                const numQuestions = parseInt($(this).find('input[name*="num_questions"]').val()) || 0;
                const hardRate = parseInt($(this).find('input[name*="hard_rate"]').val()) || 0;
                const hardCount = Math.round(numQuestions * hardRate / 100);
                if (hardCount > 0) {
                    tagDetails.push(`${tagName}: ${hardCount} câu`);
                }
            });
            hardDetail += ` - ${tagDetails.join(', ')}`;
            if (randomHard > 0) {
                hardDetail += `, ${randomHard} câu ngẫu nhiên`;
            }
        }
        hardDetail += ')';

        // Cập nhật hiển thị
        $('.easy-questions').text(easyDetail);
        $('.medium-questions').text(mediumDetail);
        $('.hard-questions').text(hardDetail);
    }

    // Xử lý khi chọn môn học
    $('#subject_select').change(function() {
        const subjectCode = $(this).val();
        $('#addTagBtn').prop('disabled', !subjectCode);
        $('#tagContainer').empty();
        calculateOverallDifficulty();
    });

    // Khởi tạo select2 cho tag
    function initializeTagSelect(element) {
        const subjectCode = $('#subject_select').val();
        $(element).select2({
            theme: 'bootstrap-5',
            placeholder: 'Chọn tag...',
            ajax: {
                url: '/questions/tags-by-subject',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        subject_code: subjectCode,
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            }
        }).on('select2:select', function(e) {
            console.log('Tag được chọn:', e.params.data);
            calculateOverallDifficulty();
        });
    }

    // Hàm để gắn event listeners cho một tag item
    function attachTagEventListeners(tagItem) {
        // Lắng nghe thay đổi select2
        tagItem.find('.tag-select').on('change', function() {
            console.log('Tag thay đổi');
            calculateOverallDifficulty();
        });

        // Lắng nghe thay đổi số câu hỏi
        tagItem.find('input[name*="num_questions"]').on('input', function() {
            console.log('Số câu hỏi thay đổi');
            calculateOverallDifficulty();
        });

        // Lắng nghe thay đổi tỷ lệ độ khó
        tagItem.find('.tag-difficulty-rate').on('input', function() {
            console.log('Tỷ lệ độ khó thay đổi');
            validateTagDifficultyRates(tagItem);
            calculateOverallDifficulty();
        });
    }

    // Validate và tính toán cho các tag có sẵn
    $('.tag-item').each(function() {
        const tagItem = $(this);
        // Validate ban đầu
        validateTagDifficultyRates(tagItem);
        // Gắn event listeners
        attachTagEventListeners(tagItem);
        // Trigger tính toán ban đầu
        calculateOverallDifficulty();
    });

    // Thêm tag mới
    $('#addTagBtn').click(function() {
        const newTagDiv = document.createElement('div');
        newTagDiv.className = 'tag-item border rounded p-3 mb-3';
        newTagDiv.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tag <span class="text-danger">*</span></label>
                    <select class="form-select tag-select" name="tags[][id]" required></select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số câu hỏi <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="tags[][num_questions]" 
                           min="1" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Tỷ lệ dễ (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control tag-difficulty-rate" 
                           name="tags[][easy_rate]" value="40" min="0" max="100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tỷ lệ trung bình (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control tag-difficulty-rate" 
                           name="tags[][medium_rate]" value="40" min="0" max="100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tỷ lệ khó (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control tag-difficulty-rate" 
                           name="tags[][hard_rate]" value="20" min="0" max="100" required>
                </div>
                <div class="col-12">
                    <div class="text-danger difficulty-rate-error mt-2" style="display: none;"></div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-tag mt-2">
                <i class="fas fa-times me-2"></i>Xóa tag
            </button>
        `;

        // Thêm vào container
        const tagItem = $(document.getElementById('tagContainer').appendChild(newTagDiv));

        // Khởi tạo select2 cho tag select
        initializeTagSelect(tagItem.find('.tag-select'));

        // Gắn event listeners cho tag mới
        attachTagEventListeners(tagItem);

        calculateOverallDifficulty();
    });

    // Xóa tag
    $(document).on('click', '.remove-tag', function() {
        console.log('Xóa tag');
        $(this).closest('.tag-item').remove();
        calculateOverallDifficulty();
    });

    // Validate tỷ lệ độ khó của tag
    function validateTagDifficultyRates(tagItem) {
        const rates = $(tagItem).find('.tag-difficulty-rate');
        let total = 0;
        rates.each(function() {
            total += parseInt($(this).val()) || 0;
        });
        
        if (total !== 100) {
            $(tagItem).find('.difficulty-rate-error').text('Tổng tỷ lệ độ khó phải bằng 100%').show();
            $(tagItem).find('.tag-difficulty-rate').addClass('is-invalid');
            return false;
        }
        $(tagItem).find('.difficulty-rate-error').hide();
        $(tagItem).find('.tag-difficulty-rate').removeClass('is-invalid');
        return true;
    }

    function calculateOverallDifficulty() {
        console.log('Tính toán tỷ lệ độ khó chung');
        const tagItems = $('.tag-item');
        const difficultyInputs = $('input[name="easy_rate"], input[name="medium_rate"], input[name="hard_rate"]');

        if (tagItems.length === 0) {
            // Nếu không có tag nào, cho phép chỉnh sửa
            difficultyInputs.prop('readonly', false);
            updateQuestionCounts();
            return;
        }

        // Nếu có tag, khóa input và tính toán tự động
        difficultyInputs.prop('readonly', true);

        let totalQuestions = 0;
        let totalEasy = 0;
        let totalMedium = 0;
        let totalHard = 0;

        tagItems.each(function() {
            const numQuestions = parseInt($(this).find('input[name*="num_questions"]').val()) || 0;
            const easyRate = parseInt($(this).find('input[name*="easy_rate"]').val()) || 0;
            const mediumRate = parseInt($(this).find('input[name*="medium_rate"]').val()) || 0;
            const hardRate = parseInt($(this).find('input[name*="hard_rate"]').val()) || 0;

            console.log('Tag data:', { numQuestions, easyRate, mediumRate, hardRate });

            totalQuestions += numQuestions;
            totalEasy += (numQuestions * easyRate / 100);
            totalMedium += (numQuestions * mediumRate / 100);
            totalHard += (numQuestions * hardRate / 100);
        });

        const examTotalQuestions = parseInt($('#total_questions').val()) || 0;
        console.log('Tổng số câu:', examTotalQuestions, 'Đã phân bổ:', totalQuestions);
        const remainingQuestions = examTotalQuestions - totalQuestions;
        
        // Nếu còn câu hỏi chưa phân bổ, chia theo tỷ lệ mặc định
        if (remainingQuestions > 0) {
            totalEasy += (remainingQuestions * 40 / 100);
            totalMedium += (remainingQuestions * 40 / 100);
            totalHard += (remainingQuestions * 20 / 100);
        }

        // Tính tỷ lệ phần trăm tổng thể
        const easyRate = Math.round((totalEasy / examTotalQuestions) * 100);
        const mediumRate = Math.round((totalMedium / examTotalQuestions) * 100);
        const hardRate = Math.round((totalHard / examTotalQuestions) * 100);

        // Cập nhật giá trị vào form
        $('input[name="easy_rate"]').val(easyRate);
        $('input[name="medium_rate"]').val(mediumRate);
        $('input[name="hard_rate"]').val(hardRate);
        updateQuestionCounts();
    }

    // Gọi hàm khi load trang và khi thay đổi giá trị
    updateQuestionCounts();
    $('#total_questions').on('input', function() {
        console.log('Tổng số câu thay đổi');
        updateQuestionCounts();
        calculateOverallDifficulty();
    });

    // Xử lý khi thay đổi tỷ lệ độ khó chung (khi không có tag)
    $('input[name="easy_rate"], input[name="medium_rate"], input[name="hard_rate"]').on('input', function() {
        if ($('.tag-item').length === 0) {
            const easyRate = parseInt($('input[name="easy_rate"]').val()) || 0;
            const mediumRate = parseInt($('input[name="medium_rate"]').val()) || 0;
            const hardRate = parseInt($('input[name="hard_rate"]').val()) || 0;
            
            // Kiểm tra tổng = 100%
            const total = easyRate + mediumRate + hardRate;
            if (total !== 100) {
                $('.difficulty-rate-error').text('Tổng tỷ lệ độ khó phải bằng 100%').show();
                $('input[name="easy_rate"], input[name="medium_rate"], input[name="hard_rate"]').addClass('is-invalid');
            } else {
                $('.difficulty-rate-error').hide();
                $('input[name="easy_rate"], input[name="medium_rate"], input[name="hard_rate"]').removeClass('is-invalid');
            }
            
            updateQuestionCounts();
        }
    });
    // Hàm kiểm tra thời gian làm bài
    function checkDuration() {
        const totalQuestions = parseInt($('#total_questions').val()) || 0;
        const duration = parseInt($('input[name="duration"]').val()) || 0;
        
        if (totalQuestions > 0 && duration > 0) {
            const timePerQuestion = duration * 60 / totalQuestions; // Thời gian mỗi câu (giây)
            const warning = $('.duration-warning');
            
            // Hàm format thời gian
            function formatTime(seconds) {
                if (seconds < 60) {
                    return `${Math.round(seconds)} giây`;
                }
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.round(seconds % 60);
                return remainingSeconds > 0 ? 
                    `${minutes} phút ${remainingSeconds} giây` : 
                    `${minutes} phút`;
            }
            
            if (timePerQuestion < 40) {
                warning.text(`Thời gian làm bài quá ngắn (${formatTime(timePerQuestion)}/câu, nên để ít nhất 40 giây/câu)`).show();
            } else if (timePerQuestion > 120) {
                warning.text(`Thời gian làm bài quá dài (${formatTime(timePerQuestion)}/câu, nên để tối đa 2 phút/câu)`).show();
            } else {
                warning.text(`Thời gian làm bài phù hợp (${formatTime(timePerQuestion)}/câu)`).show();
            }
        } else {
            $('.duration-warning').hide();
        }
    }

    // Thêm event listeners cho duration và total_questions
    $('input[name="duration"], #total_questions').on('input', function() {
        checkDuration();
    });

    // Gọi hàm kiểm tra khi load trang
    checkDuration();

    // Thêm hàm kiểm tra tổng số câu hỏi trong các tag
    function validateTotalQuestions() {
        const totalQuestions = parseInt($('#total_questions').val()) || 0;
        let tagTotalQuestions = 0;
        
        // Tính tổng số câu hỏi từ tất cả các tag
        $('.tag-item').each(function() {
            const numQuestions = parseInt($(this).find('input[name$="[num_questions]"]').val()) || 0;
            tagTotalQuestions += numQuestions;
        });
        
        // Kiểm tra nếu tổng số câu hỏi trong tag vượt quá tổng số câu đề thi
        if (tagTotalQuestions > totalQuestions) {
            $('.tag-questions-error').text('Tổng số câu hỏi trong các tag (' + tagTotalQuestions + ') không được vượt quá tổng số câu hỏi đề thi (' + totalQuestions + ')').show();
            return false;
        }
        
        $('.tag-questions-error').hide();
        return true;
    }

    // Thêm validation khi submit form
    $('#examForm').on('submit', function(e) {
        // Kiểm tra tổng số câu hỏi trong tag
        if (!validateTotalQuestions()) {
            e.preventDefault();
            return false;
        }
        
        // ... các validation khác giữ nguyên ...
    });

    // Thêm sự kiện kiểm tra khi thay đổi số câu hỏi
    $('#total_questions, .tag-item input[name$="[num_questions]"]').on('input', function() {
        validateTotalQuestions();
    });
});
</script>
@endpush 