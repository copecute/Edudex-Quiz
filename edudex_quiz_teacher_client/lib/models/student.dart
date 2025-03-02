class Student {
  final int id;
  final String studentCode;
  final String name;
  final int examId;
  String status; // not_started, in_progress, completed

  Student({
    required this.id,
    required this.studentCode,
    required this.name,
    required this.examId,
    this.status = 'not_started',
  });

  factory Student.fromMap(Map<String, dynamic> map) {
    return Student(
      id: map['id'] as int,
      studentCode: map['student_code'] as String,
      name: map['name'] as String,
      examId: map['exam_id'] as int,
      status: map['status'] as String,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'student_code': studentCode,
      'name': name,
      'exam_id': examId,
      'status': status,
    };
  }

  @override
  String toString() {
    return 'Student(id: $id, studentCode: $studentCode, name: $name, examId: $examId, status: $status)';
  }
}
