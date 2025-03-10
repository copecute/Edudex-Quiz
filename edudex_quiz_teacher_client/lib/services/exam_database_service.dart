import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'dart:convert';
import '../models/exam_schedule.dart';
import '../models/exam.dart';
import '../utils/crypto.dart' as crypto_util;

class ExamDatabaseService {
  static Database? _database;
  static final ExamDatabaseService _instance = ExamDatabaseService._internal();

  factory ExamDatabaseService() => _instance;
  ExamDatabaseService._internal();

  Future<Database> get database async {
    try {
      if (_database != null && _database!.isOpen) {
        return _database!;
      }

      // Đảm bảo đóng database cũ nếu còn mở
      await closeDatabase();

      _database = await _initDatabase();
      return _database!;
    } catch (e) {
      print('❌ Lỗi khi mở database: $e');
      // Thử tạo lại database nếu có lỗi
      await closeDatabase();
      _database = await _initDatabase();
      return _database!;
    }
  }

  Future<void> closeDatabase() async {
    if (_database != null && _database!.isOpen) {
      await _database!.close();
      _database = null;
    }
  }

  Future<Database> _initDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'exam.db');

    // Đóng database cũ nếu còn mở
    await closeDatabase();

    return await openDatabase(
      path,
      version: 3,
      readOnly: false,
      singleInstance: true,
      onConfigure: (db) async {
        // Bật foreign keys và sử dụng DELETE mode
        await db.execute('PRAGMA foreign_keys = ON');
        await db.execute('PRAGMA journal_mode = DELETE');
      },
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

        // Thêm bảng kết quả thi
        await db.execute('''
          CREATE TABLE IF NOT EXISTS exam_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_code TEXT,
            exam_code TEXT,
            full_name TEXT,
            correct_answers INTEGER,
            total_questions INTEGER,
            score REAL,
            note TEXT,
            submitted_at TEXT,
            log_file TEXT
          )
        ''');
      },
      onUpgrade: (db, oldVersion, newVersion) async {
        if (oldVersion < 2) {
          await db.execute('ALTER TABLE students ADD COLUMN token TEXT');
        }
        if (oldVersion < 3) {
          await db.execute('''
            CREATE TABLE IF NOT EXISTS exam_results (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              student_code TEXT,
              exam_code TEXT,
              full_name TEXT,
              correct_answers INTEGER,
              total_questions INTEGER,
              score REAL,
              note TEXT,
              submitted_at TEXT,
              log_file TEXT
            )
          ''');
        }
      },
      onOpen: (db) async {
        // Kiểm tra quyền ghi
        try {
          await db.rawQuery('PRAGMA journal_mode'); // Kiểm tra journal mode
          await db.rawQuery('PRAGMA synchronous'); // Kiểm tra sync mode

          await db.insert('exam_results', {
            'exam_code': 'test',
            'student_code': 'test',
            'correct_answers': 0,
            'total_questions': 0,
            'score': 0.0,
            'log_file': 'test',
            'submitted_at': DateTime.now().toIso8601String(),
          });
          await db.delete('exam_results',
              where: 'exam_code = ?', whereArgs: ['test']);
          print('✅ Database có quyền ghi');
        } catch (e) {
          print('❌ Database không có quyền ghi: $e');
          rethrow;
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
        'start_date': period.startTime.toIso8601String(),
        'end_date': period.endTime.toIso8601String(),
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

  Future<void> saveExamResult({
    required String examCode,
    required String studentCode,
    required int correctAnswers,
    required int totalQuestions,
    required double score,
    required String logFile,
    String? note,
    required DateTime submittedAt,
    required String fullName,
  }) async {
    final db = await database;
    await db.insert('exam_results', {
      'exam_code': examCode,
      'student_code': studentCode,
      'correct_answers': correctAnswers,
      'total_questions': totalQuestions,
      'score': score,
      'log_file': logFile,
      'note': note,
      'submitted_at': submittedAt.toIso8601String(),
      'full_name': fullName,
    });
  }

  Future<List<Map<String, dynamic>>> getExamQuestions() async {
    final db = await database;
    final questions = await db.query('questions');

    // Lấy đáp án đúng cho mỗi câu hỏi
    for (var q in questions) {
      final answers = await db.query(
        'answers',
        where: 'question_id = ? AND is_correct = 1',
        whereArgs: [q['id']],
      );
      if (answers.isNotEmpty) {
        q['correct_answer_id'] = answers.first['id'];
      }
    }

    return questions;
  }

  // Thêm phương thức kiểm tra đã nộp bài chưa
  Future<bool> hasSubmittedExam(String examCode, String studentCode) async {
    final db = await database;
    final results = await db.query(
      'exam_results',
      where: 'exam_code = ? AND student_code = ?',
      whereArgs: [examCode, studentCode],
    );
    return results.isNotEmpty;
  }

  // Thêm phương thức xóa toàn bộ dữ liệu
  Future<void> clearAllData() async {
    final db = await database;
    await db.transaction((txn) async {
      try {
        // Xóa dữ liệu theo thứ tự để tránh lỗi foreign key
        print('🗑️ Đang xóa dữ liệu...');

        // Xóa các bảng con trước
        await txn.execute('DELETE FROM exam_results');
        await txn.execute('DELETE FROM answers');
        await txn.execute('DELETE FROM question_tags');
        await txn.execute('DELETE FROM questions');
        await txn.execute('DELETE FROM tag_difficulty_rates');
        await txn.execute('DELETE FROM tags');
        await txn.execute('DELETE FROM exam_difficulty_rates');
        await txn.execute('DELETE FROM exams');
        await txn.execute('DELETE FROM subjects');
        await txn.execute('DELETE FROM students');
        await txn.execute('DELETE FROM test_sessions');
        await txn.execute('DELETE FROM shifts');
        await txn.execute('DELETE FROM rooms');

        print('✅ Đã xóa toàn bộ dữ liệu');
      } catch (e) {
        print('❌ Lỗi khi xóa dữ liệu: $e');
        rethrow;
      }
    });
  }

  Future<List<Map<String, dynamic>>> getExamResults() async {
    try {
      // Tạo kết nối mới mỗi lần truy vấn để tránh xung đột
      final dbPath = await getDatabasesPath();
      final path = join(dbPath, 'exam.db');

      final db = await openDatabase(
        path,
        version: 3,
        readOnly: false,
      );

      try {
        // Kiểm tra bảng có tồn tại không
        final tableExists = await _checkTableExists(db, 'exam_results');
        if (!tableExists) {
          print('⚠️ Bảng exam_results không tồn tại, đang tạo mới...');
          await db.execute('''
            CREATE TABLE IF NOT EXISTS exam_results (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              student_code TEXT,
              exam_code TEXT,
              correct_answers INTEGER,
              total_questions INTEGER,
              score REAL,
              note TEXT,
              submitted_at TEXT,
              log_file TEXT
            )
          ''');
          return [];
        }

        // Lấy kết quả từ bảng exam_results
        final results =
            await db.query('exam_results', orderBy: 'submitted_at DESC');
        print('✅ Đã tải ${results.length} kết quả');

        // Kiểm tra bảng students có tồn tại không
        final studentsTableExists = await _checkTableExists(db, 'students');

        // Xử lý kết quả
        final processedResults = <Map<String, dynamic>>[];
        for (var result in results) {
          final processedResult = Map<String, dynamic>.from(result);

          // Nếu bảng students tồn tại, lấy thông tin họ tên
          if (studentsTableExists) {
            try {
              final studentCode = processedResult['student_code'];
              final examCode = processedResult['exam_code'];

              if (studentCode != null) {
                // Tìm thông tin sinh viên từ bảng students
                final studentData = await db.query(
                  'students',
                  columns: ['full_name'],
                  where: 'student_code = ? AND exam_code = ?',
                  whereArgs: [studentCode, examCode],
                  limit: 1,
                );

                if (studentData.isNotEmpty) {
                  processedResult['full_name'] = studentData.first['full_name'];
                }
              }
            } catch (e) {
              print('⚠️ Lỗi khi lấy thông tin sinh viên: $e');
            }
          }

          // Xử lý log file nếu có
          if (processedResult['log_file'] != null) {
            try {
              processedResult['log_content'] =
                  await _decryptLogFile(processedResult['log_file']);
            } catch (e) {
              print('⚠️ Không thể đọc log file: $e');
              processedResult['log_content'] = 'Không thể đọc nội dung bài làm';
            }
          }

          processedResults.add(processedResult);
        }

        return processedResults;
      } finally {
        // Đảm bảo đóng kết nối sau khi sử dụng
        await db.close();
      }
    } catch (e) {
      print('❌ Lỗi khi lấy kết quả thi: $e');
      rethrow;
    }
  }

  // Phương thức kiểm tra bảng tồn tại (sử dụng kết nối đã mở)
  Future<bool> _checkTableExists(Database db, String tableName) async {
    try {
      final result = await db.rawQuery(
        "SELECT name FROM sqlite_master WHERE type='table' AND name=?",
        [tableName],
      );
      return result.isNotEmpty;
    } catch (e) {
      print('❌ Lỗi khi kiểm tra bảng $tableName: $e');
      return false;
    }
  }

  // Đơn giản hóa phương thức giải mã log file
  Future<String> _decryptLogFile(String base64Data) async {
    try {
      // Sử dụng trực tiếp AppCrypto từ crypto_util
      return crypto_util.AppCrypto.decryptFromBase64(base64Data);
    } catch (e) {
      print('❌ Lỗi khi giải mã log file: $e');

      // Thử phương pháp giải mã thay thế
      try {
        final bytes = base64Decode(base64Data);
        return utf8.decode(bytes);
      } catch (e2) {
        print('❌ Lỗi khi giải mã base64: $e2');

        // Trả về một phần của chuỗi base64 nếu không thể giải mã
        if (base64Data.length > 100) {
          return '${base64Data.substring(0, 100)}... (Nội dung đã được mã hóa)';
        }
        return base64Data;
      }
    }
  }
}
