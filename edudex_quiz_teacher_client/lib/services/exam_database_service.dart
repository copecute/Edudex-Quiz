import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'dart:convert';
import '../models/exam_schedule.dart';
import '../models/exam.dart';

class ExamDatabaseService {
  static Database? _database;

  Future<Database> get database async {
    _database ??= await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'exam.db');

    return await openDatabase(
      path,
      version: 2,
      onCreate: (Database db, int version) async {
        // Bảng môn học
        await db.execute('''
          CREATE TABLE subjects (
            id INTEGER PRIMARY KEY,
            code TEXT,
            name TEXT
          )
        ''');

        // Bảng đề thi
        await db.execute('''
          CREATE TABLE exams (
            id INTEGER PRIMARY KEY,
            subject_id INTEGER,
            name TEXT,
            duration INTEGER,
            total_questions INTEGER,
            description TEXT,
            random_questions_total INTEGER,
            FOREIGN KEY (subject_id) REFERENCES subjects (id)
          )
        ''');

        // Bảng tỷ lệ độ khó của đề thi
        await db.execute('''
          CREATE TABLE exam_difficulty_rates (
            exam_id INTEGER,
            difficulty TEXT,
            percentage TEXT,
            questions INTEGER,
            random_questions INTEGER,
            FOREIGN KEY (exam_id) REFERENCES exams (id)
          )
        ''');

        // Bảng chủ đề (tags)
        await db.execute('''
          CREATE TABLE tags (
            id INTEGER PRIMARY KEY,
            name TEXT,
            num_questions INTEGER
          )
        ''');

        // Bảng tỷ lệ độ khó của từng chủ đề
        await db.execute('''
          CREATE TABLE tag_difficulty_rates (
            tag_id INTEGER,
            difficulty TEXT,
            percentage TEXT,
            questions INTEGER,
            FOREIGN KEY (tag_id) REFERENCES tags (id)
          )
        ''');

        // Bảng câu hỏi
        await db.execute('''
          CREATE TABLE questions (
            id INTEGER PRIMARY KEY,
            content TEXT,
            type TEXT,
            media TEXT
          )
        ''');

        // Bảng liên kết câu hỏi - chủ đề
        await db.execute('''
          CREATE TABLE question_tags (
            question_id INTEGER,
            tag_name TEXT,
            FOREIGN KEY (question_id) REFERENCES questions (id)
          )
        ''');

        // Bảng đáp án
        await db.execute('''
          CREATE TABLE answers (
            id INTEGER PRIMARY KEY,
            question_id INTEGER,
            content TEXT,
            media TEXT,
            is_correct INTEGER,
            FOREIGN KEY (question_id) REFERENCES questions (id)
          )
        ''');

        // Thêm bảng students trong onCreate
        await db.execute('''
          CREATE TABLE students (
            id INTEGER PRIMARY KEY,
            token TEXT,
            exam_code TEXT,
            student_code TEXT,
            full_name TEXT,
            date_of_birth TEXT,
            gender TEXT,
            phone TEXT,
            seat_number INTEGER,
            address TEXT
          )
        ''');

        // Bảng kỳ thi
        await db.execute('''
          CREATE TABLE test_sessions (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            start_date TEXT NOT NULL,
            end_date TEXT NOT NULL
          )
        ''');

        // Bảng ca thi
        await db.execute('''
          CREATE TABLE shifts (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL
          )
        ''');

        // Bảng phòng thi
        await db.execute('''
          CREATE TABLE rooms (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            location TEXT NOT NULL,
            capacity INTEGER NOT NULL
          )
        ''');
      },
      onUpgrade: (db, oldVersion, newVersion) async {
        if (oldVersion < 2) {
          // Thêm cột token vào bảng students nếu chưa có
          await db.execute('ALTER TABLE students ADD COLUMN token TEXT');
        }
      },
    );
  }

  // Cập nhật dữ liệu đề thi
  Future<void> updateExamData(Map<String, dynamic> examData) async {
    final db = await database;
    await db.transaction((txn) async {
      // Xóa dữ liệu cũ
      await txn.delete('answers');
      await txn.delete('question_tags');
      await txn.delete('questions');
      await txn.delete('tag_difficulty_rates');
      await txn.delete('tags');
      await txn.delete('exam_difficulty_rates');
      await txn.delete('exams');
      await txn.delete('subjects');

      // Thêm thông tin môn học
      final subject = examData['subject'];
      await txn.insert('subjects', {
        'id': subject['id'],
        'code': subject['code'],
        'name': subject['name'],
      });

      // Thêm thông tin đề thi
      final exam = examData['exam'];
      await txn.insert('exams', {
        'id': exam['id'],
        'subject_id': subject['id'],
        'name': exam['name'],
        'duration': exam['duration'],
        'total_questions': exam['total_questions'],
        'description': exam['description'],
        'random_questions_total': exam['random_questions_total'],
      });

      // Thêm tỷ lệ độ khó của đề thi
      final difficultyRates = exam['difficulty_rates'];
      for (var difficulty in ['easy', 'medium', 'hard']) {
        final rate = difficultyRates[difficulty];
        await txn.insert('exam_difficulty_rates', {
          'exam_id': exam['id'],
          'difficulty': difficulty,
          'percentage': rate['percentage'],
          'questions': rate['questions'],
          'random_questions': rate['random_questions'],
        });
      }

      // Thêm thông tin tags và tỷ lệ độ khó
      for (var tag in examData['tags']) {
        await txn.insert('tags', {
          'id': tag['id'],
          'name': tag['name'],
          'num_questions': tag['num_questions'],
        });

        final tagRates = tag['difficulty_rates'];
        for (var difficulty in ['easy', 'medium', 'hard']) {
          final rate = tagRates[difficulty];
          await txn.insert('tag_difficulty_rates', {
            'tag_id': tag['id'],
            'difficulty': difficulty,
            'percentage': rate['percentage'],
            'questions': rate['questions'],
          });
        }
      }

      // Thêm câu hỏi và đáp án
      for (var question in examData['questions']) {
        await txn.insert('questions', {
          'id': question['id'],
          'content': question['content'],
          'type': question['type'],
          'media': question['media'],
        });

        // Thêm tags của câu hỏi
        for (var tag in question['tags']) {
          await txn.insert('question_tags', {
            'question_id': question['id'],
            'tag_name': tag,
          });
        }

        // Thêm đáp án
        for (var answer in question['answers']) {
          await txn.insert('answers', {
            'id': answer['id'],
            'question_id': question['id'],
            'content': answer['content'],
            'media': answer['media'],
            'is_correct': answer['is_correct'] ? 1 : 0,
          });
        }
      }
    });
  }

  // Lấy thông tin đề thi
  Future<Map<String, dynamic>?> getExamData() async {
    final db = await database;
    final List<Map<String, dynamic>> subjects = await db.query('subjects');
    if (subjects.isEmpty) return null;

    final subject = subjects.first;
    final List<Map<String, dynamic>> exams = await db.query(
      'exams',
      where: 'subject_id = ?',
      whereArgs: [subject['id']],
    );
    final exam = exams.first;

    // Lấy tỷ lệ độ khó của đề thi
    final List<Map<String, dynamic>> examRates = await db.query(
      'exam_difficulty_rates',
      where: 'exam_id = ?',
      whereArgs: [exam['id']],
    );

    // Lấy thông tin tags
    final List<Map<String, dynamic>> tags = await db.query('tags');
    final List<Map<String, dynamic>> formattedTags = [];

    for (var tag in tags) {
      final List<Map<String, dynamic>> tagRates = await db.query(
        'tag_difficulty_rates',
        where: 'tag_id = ?',
        whereArgs: [tag['id']],
      );

      final Map<String, dynamic> formattedTag = {
        ...tag,
        'difficulty_rates': {
          for (var rate in tagRates)
            rate['difficulty']: {
              'percentage': rate['percentage'],
              'questions': rate['questions'],
            }
        }
      };
      formattedTags.add(formattedTag);
    }

    // Lấy câu hỏi và đáp án
    final List<Map<String, dynamic>> questions = await db.query('questions');
    final List<Map<String, dynamic>> formattedQuestions = [];

    for (var question in questions) {
      final List<Map<String, dynamic>> questionTags = await db.query(
        'question_tags',
        where: 'question_id = ?',
        whereArgs: [question['id']],
      );

      final List<Map<String, dynamic>> answers = await db.query(
        'answers',
        where: 'question_id = ?',
        whereArgs: [question['id']],
      );

      final Map<String, dynamic> formattedQuestion = {
        ...question,
        'tags': questionTags.map((t) => t['tag_name']).toList(),
        'answers': answers
            .map((a) => {
                  ...a,
                  'is_correct': a['is_correct'] == 1,
                })
            .toList(),
      };
      formattedQuestions.add(formattedQuestion);
    }

    return {
      'subject': subject,
      'exam': {
        ...exam,
        'difficulty_rates': {
          for (var rate in examRates)
            rate['difficulty']: {
              'percentage': rate['percentage'],
              'questions': rate['questions'],
              'random_questions': rate['random_questions'],
            }
        }
      },
      'tags': formattedTags,
      'questions': formattedQuestions,
    };
  }

  // Thêm phương thức updateStudents
  Future<void> updateStudents(List<dynamic> students) async {
    final db = await database;
    await db.transaction((txn) async {
      await txn.delete('students');
      for (var student in students) {
        await txn.insert('students', {
          'exam_code': student['exam_code'],
          'student_code': student['student_code'],
          'full_name': student['full_name'],
          'date_of_birth': student['date_of_birth'],
          'gender': student['gender'],
          'phone': student['phone'],
          'seat_number': student['seat_number'],
          'address': student['address'],
        });
      }
    });
  }

  // Lấy danh sách thí sinh
  Future<List<Map<String, dynamic>>> getStudents() async {
    final db = await database;
    return await db.query('students', orderBy: 'seat_number ASC');
  }

  // Lấy thông tin sinh viên theo số báo danh và mã sinh viên
  Future<Map<String, dynamic>?> getStudentByExamCode(
      String examCode, String studentCode) async {
    final db = await database;
    final List<Map<String, dynamic>> results = await db.query(
      'students',
      where: 'exam_code = ? AND student_code = ?',
      whereArgs: [examCode, studentCode],
    );

    if (results.isEmpty) return null;
    return results.first;
  }

  // Cập nhật token cho sinh viên
  Future<void> updateStudentToken(int studentId, String token) async {
    final db = await database;
    await db.update(
      'students',
      {'token': token},
      where: 'id = ?',
      whereArgs: [studentId],
    );
  }

  // Lấy thông tin sinh viên theo token
  Future<Map<String, dynamic>?> getStudentByToken(String token) async {
    final db = await database;
    final List<Map<String, dynamic>> results = await db.query(
      'students',
      where: 'token = ?',
      whereArgs: [token],
    );

    if (results.isEmpty) return null;
    return results.first;
  }

  // Thêm method để lưu thông tin kỳ thi, ca thi và phòng thi
  Future<void> saveSessionInfo(
      ExamPeriod period, ExamShift shift, ExamRoom room) async {
    final db = await database;
    await db.transaction((txn) async {
      // Xóa dữ liệu cũ
      await txn.delete('test_sessions');
      await txn.delete('shifts');
      await txn.delete('rooms');

      // Lưu thông tin kỳ thi
      await txn.insert('test_sessions', {
        'id': period.id,
        'name': period.name,
        'start_date': period.startDate.toIso8601String(),
        'end_date': period.endDate.toIso8601String(),
      });

      // Lưu thông tin ca thi
      await txn.insert('shifts', {
        'id': shift.id,
        'name': shift.name,
        'start_time': shift.startTime.toIso8601String(),
        'end_time': shift.endTime.toIso8601String(),
      });

      // Lưu thông tin phòng thi
      await txn.insert('rooms', {
        'id': room.id,
        'name': room.name,
        'location': room.location,
        'capacity': room.capacity,
      });
    });
  }

  // Method để lấy thông tin phiên thi
  Future<Map<String, dynamic>?> getSessionInfo() async {
    final db = await database;

    final List<Map<String, dynamic>> sessions = await db.query('test_sessions');
    if (sessions.isEmpty) return null;

    final List<Map<String, dynamic>> shifts = await db.query('shifts');
    final List<Map<String, dynamic>> rooms = await db.query('rooms');

    return {
      'test_session': sessions.first,
      'shift': shifts.isNotEmpty ? shifts.first : null,
      'room': rooms.isNotEmpty ? rooms.first : null,
    };
  }
}
