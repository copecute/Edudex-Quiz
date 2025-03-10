class ExamPeriod {
  final int id;
  final String name;
  final DateTime startTime;
  final DateTime endTime;
  final List<ExamShift> shifts;

  ExamPeriod({
    required this.id,
    required this.name,
    required this.startTime,
    required this.endTime,
    required this.shifts,
  });

  factory ExamPeriod.fromJson(Map<String, dynamic> json) {
    final examPeriod = json['exam_period'];
    return ExamPeriod(
      id: examPeriod['id'],
      name: examPeriod['name']?.toString() ?? 'Không có tên',
      startTime: DateTime.parse(
          examPeriod['start_time'] ?? DateTime.now().toIso8601String()),
      endTime: DateTime.parse(
          examPeriod['end_time'] ?? DateTime.now().toIso8601String()),
      shifts: (json['shifts'] as List?)
              ?.map((shift) => ExamShift.fromJson(shift))
              .toList() ??
          [],
    );
  }
}

class ExamShift {
  final int id;
  final String name;
  final DateTime startTime;
  final DateTime endTime;
  final List<ExamRoom> rooms;

  ExamShift({
    required this.id,
    required this.name,
    required this.startTime,
    required this.endTime,
    required this.rooms,
  });

  factory ExamShift.fromJson(Map<String, dynamic> json) {
    return ExamShift(
      id: json['id'],
      name: json['name']?.toString() ?? 'Không có tên',
      startTime: DateTime.parse(
          json['start_time'] ?? DateTime.now().toIso8601String()),
      endTime:
          DateTime.parse(json['end_time'] ?? DateTime.now().toIso8601String()),
      rooms: (json['rooms'] as List?)
              ?.map((room) => ExamRoom.fromJson(room))
              .toList() ??
          [],
    );
  }
}

class ExamRoom {
  final int? id;
  final String? code;
  final String name;
  final String location;
  final int capacity;
  final Subject subject;

  ExamRoom({
    this.id,
    this.code,
    required this.name,
    required this.location,
    required this.capacity,
    required this.subject,
  });

  factory ExamRoom.fromJson(Map<String, dynamic> json) {
    return ExamRoom(
      id: json['id'],
      code: json['code']?.toString(),
      name: json['name']?.toString() ?? 'Không có tên',
      location: json['facility']?.toString() ?? 'Không có địa điểm',
      capacity: json['capacity'] ?? 0,
      subject: Subject.fromJson(json['subject'] ?? {}),
    );
  }
}

class Subject {
  final int? id;
  final String name;
  final Exam? exam;

  Subject({
    this.id,
    required this.name,
    this.exam,
  });

  factory Subject.fromJson(Map<String, dynamic> json) {
    return Subject(
      id: json['id'],
      name: json['name']?.toString() ?? 'Không có tên môn học',
      exam: json['exam'] != null ? Exam.fromJson(json['exam']) : null,
    );
  }
}

class Exam {
  final int? id;
  final String? name;
  final int? duration;
  final int? totalQuestions;

  Exam({
    this.id,
    this.name,
    this.duration,
    this.totalQuestions,
  });

  factory Exam.fromJson(Map<String, dynamic> json) {
    return Exam(
      id: json['id'],
      name: json['name']?.toString(),
      duration: json['duration'],
      totalQuestions: json['total_questions'],
    );
  }
}
