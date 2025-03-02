import 'dart:convert';

class Submission {
  final int id;
  final int studentId;
  final int examId;
  final Map<String, String> answers;
  final double score;
  final int correctCount;
  final DateTime submittedAt;

  Submission({
    required this.id,
    required this.studentId,
    required this.examId,
    required this.answers,
    required this.score,
    required this.correctCount,
    required this.submittedAt,
  });

  factory Submission.fromMap(Map<String, dynamic> map) {
    return Submission(
      id: map['id'] as int,
      studentId: map['student_id'] as int,
      examId: map['exam_id'] as int,
      answers: Map<String, String>.from(json.decode(map['answers'])),
      score: map['score'] as double,
      correctCount: map['correct_count'] as int,
      submittedAt: DateTime.parse(map['submitted_at'] as String),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'student_id': studentId,
      'exam_id': examId,
      'answers': json.encode(answers),
      'score': score,
      'correct_count': correctCount,
      'submitted_at': submittedAt.toIso8601String(),
    };
  }

  @override
  String toString() {
    return 'Submission(id: $id, studentId: $studentId, examId: $examId, answers: $answers, score: $score, correctCount: $correctCount, submittedAt: $submittedAt)';
  }
}
