@extends('layouts.app')

@section('title', 'Tạo đề thi mới - Edudex Quiz')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.error-feedback {
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tạo đề thi mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('test_papers.store') }}" method="POST" id="createTestPaperForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Tên đề thi</label>
                            <input type="text" name="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thời gian làm bài (phút)</label>
                                <input type="number" name="duration" 
                                       class="form-control @error('duration') is-invalid @enderror"
                                       value="{{ old('duration') }}" min="1" required>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="durationWarning" class="text-warning small mt-1 d-none"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tổng số câu hỏi</label>
                                <input type="number" name="total_questions" 
                                       class="form-control @error('total_questions') is-invalid @enderror"
                                       value="{{ old('total_questions') }}" min="1" required>
                                @error('total_questions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Tỉ lệ độ khó của đề thi</label>
                            <div class="text-muted small mb-2">
                                <i class="fas fa-info-circle"></i> 
                                Khi không chọn tags, bạn có thể điều chỉnh tỉ lệ độ khó thủ công. 
                                Khi có tags, tỉ lệ sẽ được tính tự động.
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" name="easy_rate" 
                                               class="form-control paper-rate @error('easy_rate') is-invalid @enderror"
                                               value="{{ old('easy_rate', 50.00) }}" 
                                               min="0" max="100" step="0.01" required>
                                        <span class="input-group-text">Dễ</span>
                                    </div>
                                    <div class="text-muted small mt-1" id="easyDetail"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" name="medium_rate" 
                                               class="form-control paper-rate @error('medium_rate') is-invalid @enderror"
                                               value="{{ old('medium_rate', 30.00) }}" 
                                               min="0" max="100" step="0.01" required>
                                        <span class="input-group-text">TB</span>
                                    </div>
                                    <div class="text-muted small mt-1" id="mediumDetail"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" name="hard_rate" 
                                               class="form-control paper-rate @error('hard_rate') is-invalid @enderror"
                                               value="{{ old('hard_rate', 20.00) }}" 
                                               min="0" max="100" step="0.01" required>
                                        <span class="input-group-text">Khó</span>
                                    </div>
                                    <div class="text-muted small mt-1" id="hardDetail"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Môn học</label>
                            <select name="subject_id" id="subject_id"
                                    class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Chọn môn học</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" 
                                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="tagsContainer" class="mb-3 d-none">
                            <label class="form-label">Phân bố câu hỏi theo tags</label>
                            <div id="tagsList"></div>
                            <div id="remainingQuestions" class="alert alert-info d-none">
                                Số câu hỏi còn lại (<span class="remaining-count"></span> câu) sẽ được lấy ngẫu nhiên trong ngân hàng câu hỏi
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('test_papers.index') }}" class="btn btn-light me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Tạo mới</button>
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
    // Xử lý khi chọn môn học
    $('#subject_id').on('change', function() {
        const subjectId = $(this).val();
        if (subjectId) {
            // Lấy tags của môn học
            $.get(`/test_papers/tags/${subjectId}`, function(tags) {
                if (tags.length > 0) {
                    $('#tagsContainer').removeClass('d-none');
                    renderTagSelection(tags);
                } else {
                    $('#tagsContainer').addClass('d-none');
                    $('#tagsList').html('<div class="alert alert-warning">Môn học này chưa có tags</div>');
                }
            });
        } else {
            $('#tagsContainer').addClass('d-none');
            $('#tagsList').empty();
        }
    });

    // Render lựa chọn tags
    function renderTagSelection(tags) {
        const html = `
            <div class="mb-3">
                <select class="form-select select2-tags" multiple>
                    ${tags.map(tag => `
                        <option value="${tag.id}" data-tag='${JSON.stringify(tag)}'>
                            ${tag.name}
                        </option>
                    `).join('')}
                </select>
            </div>
            <div id="tagDetails" class="d-none">
                <h6 class="mb-3">Chi tiết phân bố câu hỏi</h6>
                <div id="tagDetailsContent"></div>
            </div>
        `;
        
        $('#tagsList').html(html);

        // Khởi tạo Select2
        $('.select2-tags').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Chọn tags cho đề thi',
            allowClear: true
        });

        // Xử lý khi chọn/bỏ chọn tags
        $('.select2-tags').on('change', function() {
            const selectedTags = $(this).find(':selected').map(function() {
                return $(this).data('tag');
            }).get();

            if (selectedTags.length > 0) {
                $('#tagDetails').removeClass('d-none');
                renderTagDetails(selectedTags);
            } else {
                $('#tagDetails').addClass('d-none');
                $('#tagDetailsContent').empty();
            }
            validateForm();
        });
    }

    // Cập nhật lại hàm getSelectedTags
    function getSelectedTags() {
        return $('.select2-tags').find(':selected').map(function() {
            return $(this).data('tag');
        }).get();
    }

    // Thêm hàm lưu giá trị đã nhập
    function getTagInputValues() {
        const values = {};
        $('#tagDetailsContent .card-body').each(function() {
            const tagId = $(this).find('input[type="hidden"]').val();
            values[tagId] = {
                num_questions: $(this).find('.tag-num-questions').val(),
                easy_rate: $(this).find('input[name$="[easy_rate]"]').val(),
                medium_rate: $(this).find('input[name$="[medium_rate]"]').val(),
                hard_rate: $(this).find('input[name$="[hard_rate]"]').val()
            };
        });
        return values;
    }

    // Sửa lại hàm renderTagDetails
    function renderTagDetails(selectedTags) {
        // Lưu giá trị hiện tại
        const savedValues = getTagInputValues();
        
        const html = selectedTags.map(tag => {
            const savedValue = savedValues[tag.id] || {};
            return `
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">${tag.name}</h6>
                        <input type="hidden" name="tags[${tag.id}][id]" value="${tag.id}">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Số câu hỏi</label>
                                <input type="number" name="tags[${tag.id}][num_questions]" 
                                       class="form-control tag-num-questions" min="0" required
                                       value="${savedValue.num_questions || ''}">
                                <div class="error-feedback d-none text-danger small"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tỉ lệ độ khó (%)</label>
                                <div class="input-group">
                                    <input type="number" name="tags[${tag.id}][easy_rate]" 
                                           class="form-control tag-rate" placeholder="Dễ" 
                                           min="0" max="100" required
                                           value="${savedValue.easy_rate || ''}">
                                    <input type="number" name="tags[${tag.id}][medium_rate]" 
                                           class="form-control tag-rate" placeholder="TB" 
                                           min="0" max="100" required
                                           value="${savedValue.medium_rate || ''}">
                                    <input type="number" name="tags[${tag.id}][hard_rate]" 
                                           class="form-control tag-rate" placeholder="Khó" 
                                           min="0" max="100" required
                                           value="${savedValue.hard_rate || ''}">
                                </div>
                                <div class="error-feedback d-none text-danger small">Tổng tỉ lệ độ khó phải bằng 100%</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        $('#tagDetailsContent').html(html);

        // Validate khi thay đổi giá trị
        $('.tag-num-questions, .tag-rate').on('change', validateForm);
        
        // Tính lại tỉ lệ độ khó tổng sau khi render
        calculateTotalDifficultyRates();
    }

    // Validate form
    function validateForm() {
        let isValid = true;
        
        // Validate tỉ lệ độ khó của đề thi (luôn kiểm tra)
        const paperRates = $('.paper-rate').map(function() {
            return parseFloat($(this).val()) || 0;
        }).get();
        
        const totalPaperRate = paperRates.reduce((a, b) => a + b, 0);
        if (Math.abs(totalPaperRate - 100) > 0.01) {
            $('.paper-rate').addClass('is-invalid');
            $('#paperRateError').removeClass('d-none');
            isValid = false;
        } else {
            $('.paper-rate').removeClass('is-invalid');
            $('#paperRateError').addClass('d-none');
        }

        const selectedTags = getSelectedTags();
        if (selectedTags.length === 0) {
            return isValid; // Chỉ validate tỉ lệ tổng = 100% khi không có tags
        }

        // Validate tổng số câu hỏi và tính toán tỉ lệ độ khó
        let totalQuestions = 0;
        $('.tag-num-questions').each(function() {
            totalQuestions += parseInt($(this).val()) || 0;
        });

        const targetQuestions = parseInt($('input[name="total_questions"]').val()) || 0;
        const remainingQuestions = targetQuestions - totalQuestions;
        
        // Hiển thị số câu hỏi còn lại và tính lại tỉ lệ độ khó
        if (remainingQuestions > 0) {
            $('#remainingQuestions').removeClass('d-none');
            $('.remaining-count').text(remainingQuestions);
            $('.tag-num-questions').each(function() {
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').addClass('d-none');
            });
            calculateTotalDifficultyRates();
        } else if (remainingQuestions === 0) {
            $('#remainingQuestions').addClass('d-none');
            $('.tag-num-questions').each(function() {
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').addClass('d-none');
            });
            calculateTotalDifficultyRates();
        } else {
            $('#remainingQuestions').addClass('d-none');
            $('.tag-num-questions').each(function() {
                $(this).addClass('is-invalid');
                const errorDiv = $(this).next('.error-feedback');
                errorDiv.removeClass('d-none')
                       .text(`Tổng số câu hỏi của các tags (${totalQuestions}) không được vượt quá số câu hỏi của đề (${targetQuestions})`);
            });
            isValid = false;
        }

        // Validate tỉ lệ độ khó cho từng tag
        $('#tagDetailsContent .card-body').each(function() {
            const rates = $(this).find('.tag-rate').map(function() {
                return parseFloat($(this).val()) || 0;
            }).get();
            
            const total = rates.reduce((a, b) => a + b, 0);
            const rateInputs = $(this).find('.tag-rate');
            const errorDiv = $(this).find('.error-feedback');
            
            if (Math.abs(total - 100) > 0.01) {
                rateInputs.addClass('is-invalid');
                errorDiv.removeClass('d-none');
                isValid = false;
            } else {
                rateInputs.removeClass('is-invalid');
                errorDiv.addClass('d-none');
            }
        });

        return isValid;
    }

    // Chạy validate khi load trang
    validateForm();

    // Thêm validate khi thay đổi tổng số câu hỏi
    $('input[name="total_questions"]').on('change', validateForm);

    // Thêm validate khi thay đổi tỉ lệ độ khó
    $('.paper-rate').on('change', validateForm);

    // Validate trước khi submit
    $('#createTestPaperForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Thêm hàm tính toán tỉ lệ độ khó tổng
    function calculateTotalDifficultyRates() {
        const selectedTags = getSelectedTags();
        
        // Nếu không có tags, cho phép sửa tỉ lệ độ khó thủ công và tính số câu
        if (selectedTags.length === 0) {
            $('input[name="easy_rate"]').prop('readonly', false);
            $('input[name="medium_rate"]').prop('readonly', false);
            $('input[name="hard_rate"]').prop('readonly', false);
            calculateQuestionsByRate();
            return;
        }

        // Nếu có tags, khóa input và tính toán tự động
        $('input[name="easy_rate"]').prop('readonly', true);
        $('input[name="medium_rate"]').prop('readonly', true);
        $('input[name="hard_rate"]').prop('readonly', true);

        let totalEasy = 0;
        let totalMedium = 0;
        let totalHard = 0;
        let totalQuestions = 0;

        // Object để lưu chi tiết số câu từ mỗi tag
        let details = {
            easy: [],
            medium: [],
            hard: []
        };

        // Tính tổng số câu và tỉ lệ từng tag
        $('.tag-num-questions').each(function(index) {
            const numQuestions = parseInt($(this).val()) || 0;
            const cardBody = $(this).closest('.card-body');
            const tagName = cardBody.find('.card-title').text();
            const easyRate = parseFloat(cardBody.find('input[name$="[easy_rate]"]').val()) || 0;
            const mediumRate = parseFloat(cardBody.find('input[name$="[medium_rate]"]').val()) || 0;
            const hardRate = parseFloat(cardBody.find('input[name$="[hard_rate]"]').val()) || 0;

            const easyQuestions = Math.round(numQuestions * easyRate / 100);
            const mediumQuestions = Math.round(numQuestions * mediumRate / 100);
            const hardQuestions = Math.round(numQuestions * hardRate / 100);

            if (easyQuestions > 0) details.easy.push(`${tagName}: ${easyQuestions} câu`);
            if (mediumQuestions > 0) details.medium.push(`${tagName}: ${mediumQuestions} câu`);
            if (hardQuestions > 0) details.hard.push(`${tagName}: ${hardQuestions} câu`);

            totalQuestions += numQuestions;
            totalEasy += easyQuestions;
            totalMedium += mediumQuestions;
            totalHard += hardQuestions;
        });

        // Tính số câu còn lại và phân bổ theo tỉ lệ mặc định
        const targetQuestions = parseInt($('input[name="total_questions"]').val()) || 0;
        const remainingQuestions = targetQuestions - totalQuestions;

        if (remainingQuestions > 0) {
            const remainingEasy = Math.round(remainingQuestions * 0.5);
            const remainingMedium = Math.round(remainingQuestions * 0.3);
            const remainingHard = Math.round(remainingQuestions * 0.2);

            if (remainingEasy > 0) details.easy.push(`Random: ${remainingEasy} câu`);
            if (remainingMedium > 0) details.medium.push(`Random: ${remainingMedium} câu`);
            if (remainingHard > 0) details.hard.push(`Random: ${remainingHard} câu`);

            totalEasy += remainingEasy;
            totalMedium += remainingMedium;
            totalHard += remainingHard;
            totalQuestions = targetQuestions;
        }

        // Cập nhật input và chi tiết
        if (totalQuestions > 0) {
            $('input[name="easy_rate"]').val((totalEasy / totalQuestions * 100).toFixed(2));
            $('input[name="medium_rate"]').val((totalMedium / totalQuestions * 100).toFixed(2));
            $('input[name="hard_rate"]').val((totalHard / totalQuestions * 100).toFixed(2));

            // Hiển thị chi tiết
            $('#easyDetail').html(`${totalEasy} câu (${details.easy.join(', ')})`);
            $('#mediumDetail').html(`${totalMedium} câu (${details.medium.join(', ')})`);
            $('#hardDetail').html(`${totalHard} câu (${details.hard.join(', ')})`);
        }
    }

    // Thêm sự kiện để tính lại tỉ lệ khi thay đổi
    $(document).on('change', '.tag-num-questions, .tag-rate', calculateTotalDifficultyRates);

    // Thêm hàm kiểm tra thời gian làm bài
    function validateDuration() {
        const duration = parseInt($('input[name="duration"]').val()) || 0;
        const totalQuestions = parseInt($('input[name="total_questions"]').val()) || 0;
        
        if (duration > 0 && totalQuestions > 0) {
            const timePerQuestion = (duration * 60) / totalQuestions; // Chuyển về giây
            let warningMessage = '';

            if (timePerQuestion < 50) {
                warningMessage = `<i class="fas fa-exclamation-triangle"></i> Thời gian làm bài quá ngắn (${(timePerQuestion).toFixed(1)} giây/câu). Nên để ít nhất 50 giây cho mỗi câu.`;
            } else if (timePerQuestion > 120) {
                warningMessage = `<i class="fas fa-exclamation-triangle"></i> Thời gian làm bài khá dài (${(timePerQuestion/60).toFixed(1)} phút/câu). Nên để tối đa 2 phút cho mỗi câu.`;
            }

            if (warningMessage) {
                $('#durationWarning').removeClass('d-none').html(warningMessage);
            } else {
                $('#durationWarning').addClass('d-none');
            }
        } else {
            $('#durationWarning').addClass('d-none');
        }
    }

    // Thêm validate thời gian khi thay đổi
    $('input[name="duration"], input[name="total_questions"]').on('change', validateDuration);

    // Chạy validate khi load trang
    validateDuration();

    // Thêm validate khi thay đổi tỉ lệ độ khó thủ công
    $('.paper-rate').on('change', function() {
        const selectedTags = getSelectedTags();
        if (selectedTags.length === 0) {
            validateForm();
        }
    });

    // Thêm hàm tính số câu theo tỉ lệ
    function calculateQuestionsByRate() {
        const totalQuestions = parseInt($('input[name="total_questions"]').val()) || 0;
        if (totalQuestions > 0) {
            const easyRate = parseFloat($('input[name="easy_rate"]').val()) || 0;
            const mediumRate = parseFloat($('input[name="medium_rate"]').val()) || 0;
            const hardRate = parseFloat($('input[name="hard_rate"]').val()) || 0;

            const easyQuestions = Math.round(totalQuestions * easyRate / 100);
            const mediumQuestions = Math.round(totalQuestions * mediumRate / 100);
            const hardQuestions = Math.round(totalQuestions * hardRate / 100);

            // Hiển thị chi tiết
            if (easyQuestions > 0) {
                $('#easyDetail').html(`${easyQuestions} câu (Random: ${easyQuestions} câu)`);
            }
            if (mediumQuestions > 0) {
                $('#mediumDetail').html(`${mediumQuestions} câu (Random: ${mediumQuestions} câu)`);
            }
            if (hardQuestions > 0) {
                $('#hardDetail').html(`${hardQuestions} câu (Random: ${hardQuestions} câu)`);
            }
        } else {
            // Xóa chi tiết nếu không có câu hỏi
            $('#easyDetail').empty();
            $('#mediumDetail').empty();
            $('#hardDetail').empty();
        }
    }

    // Thêm sự kiện khi thay đổi tổng số câu
    $('input[name="total_questions"]').on('change', function() {
        const selectedTags = getSelectedTags();
        if (selectedTags.length === 0) {
            calculateQuestionsByRate();
        }
    });

    // Thêm sự kiện khi thay đổi tỉ lệ
    $('.paper-rate').on('change', function() {
        const selectedTags = getSelectedTags();
        if (selectedTags.length === 0) {
            calculateQuestionsByRate();
        }
    });

    // Chạy tính toán khi load trang
    calculateQuestionsByRate();
});
</script>
@endpush
@endsection 