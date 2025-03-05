$table->longText('log_file')->comment('File log .edudex base64'); 

public function up(): void
{
    Schema::create('exam_results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_period_id')->constrained('exam_periods')->comment('ID kỳ thi');
        $table->foreignId('exam_shift_id')->constrained('exam_shifts')->comment('ID ca thi');
        $table->foreignId('exam_period_subject_id')->constrained('exam_period_subjects')->comment('ID môn thi');
        $table->foreignId('exam_id')->constrained('exams')->comment('ID đề thi');
        $table->foreignId('exam_period_room_id')->constrained('exam_period_rooms')->comment('ID phòng thi');
        $table->foreignId('exam_period_proctor_id')->constrained('exam_period_proctors')->comment('ID giám thị');
        $table->foreignId('exam_period_subject_student_id')->constrained('exam_period_subject_students')->comment('ID thí sinh');
        // ... các trường khác giữ nguyên ...

        // Thêm unique constraint để đảm bảo mỗi thí sinh chỉ nộp 1 lần cho 1 môn trong 1 kỳ thi
        $table->unique([
            'exam_period_id',
            'exam_period_subject_id',
            'exam_period_subject_student_id'
        ], 'unique_student_subject_result');
    });
} 