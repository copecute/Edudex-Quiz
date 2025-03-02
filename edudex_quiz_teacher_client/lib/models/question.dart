import 'dart:convert';

class Question {
  final int id;
  final int examId;
  final String question;
  final List<String> options;
  final String correctAnswer;

  Question({
    required this.id,
    required this.examId,
    required this.question,
    required this.options,
    required this.correctAnswer,
  });

  factory Question.fromMap(Map<String, dynamic> map) {
    return Question(
      id: map['id'] as int,
      examId: map['exam_id'] as int,
      question: map['question'] as String,
      options: List<String>.from(json.decode(map['options'])),
      correctAnswer: map['correct_answer'] as String,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'exam_id': examId,
      'question': question,
      'options': json.encode(options),
      'correct_answer': correctAnswer,
    };
  }

  @override
  String toString() {
    return 'Question(id: $id, examId: $examId, question: $question, options: $options, correctAnswer: $correctAnswer)';
  }
}
