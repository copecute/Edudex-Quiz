class Exam {
  final int id;
  final String title;
  final int duration; // in minutes
  final int totalQuestions;

  Exam({
    required this.id,
    required this.title,
    required this.duration,
    required this.totalQuestions,
  });

  factory Exam.fromMap(Map<String, dynamic> map) {
    return Exam(
      id: map['id'] as int,
      title: map['title'] as String,
      duration: map['duration'] as int,
      totalQuestions: map['total_questions'] as int,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'title': title,
      'duration': duration,
      'total_questions': totalQuestions,
    };
  }

  @override
  String toString() {
    return 'Exam(id: $id, title: $title, duration: $duration, totalQuestions: $totalQuestions)';
  }
}
