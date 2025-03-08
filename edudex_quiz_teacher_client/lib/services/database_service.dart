import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/student.dart';
import '../models/exam.dart';
import '../models/question.dart';
import '../models/submission.dart';

class DatabaseService {
  static Database? _database;

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB();
    return _database!;
  }

  Future<Database> _initDB() async {
    String path = join(await getDatabasesPath(), 'teacher.db');
    return await openDatabase(
      path,
      version: 1,
      onCreate: (Database db, int version) async {
        // Tạo bảng students
        await db.execute('''
          CREATE TABLE students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NULL,
            student_code TEXT NOT NULL,
            name TEXT NOT NULL,
            exam_id INTEGER NOT NULL,
            status TEXT DEFAULT 'not_started'
          )
        ''');

        // Tạo bảng exams
        await db.execute('''
          CREATE TABLE exams (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            duration INTEGER NOT NULL,
            total_questions INTEGER NOT NULL
          )
        ''');

        // Tạo bảng questions
        await db.execute('''
          CREATE TABLE questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            exam_id INTEGER NOT NULL,
            question TEXT NOT NULL,
            options TEXT NOT NULL,
            correct_answer TEXT NOT NULL
          )
        ''');

        // Tạo bảng submissions
        await db.execute('''
          CREATE TABLE submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            exam_id INTEGER NOT NULL,
            answers TEXT NOT NULL,
            score REAL NOT NULL,
            correct_count INTEGER NOT NULL,
            submitted_at TEXT NOT NULL
          )
        ''');
      },
    );
  }

  // Student methods
  Future<int> insertStudent(Student student) async {
    final db = await database;
    return await db.insert('students', student.toMap());
  }

  Future<List<Student>> getStudentsByExam(int examId) async {
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'students',
      where: 'exam_id = ?',
      whereArgs: [examId],
    );
    return List.generate(maps.length, (i) => Student.fromMap(maps[i]));
  }

  Future<void> updateStudentStatus(int studentId, String status) async {
    final db = await database;
    await db.update(
      'students',
      {'status': status},
      where: 'id = ?',
      whereArgs: [studentId],
    );
  }

  // Exam methods
  Future<int> insertExam(Exam exam) async {
    final db = await database;
    return await db.insert('exams', exam.toMap());
  }

  Future<Exam?> getExam(int examId) async {
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'exams',
      where: 'id = ?',
      whereArgs: [examId],
    );
    if (maps.isEmpty) return null;
    return Exam.fromMap(maps.first);
  }

  // Question methods
  Future<int> insertQuestion(Question question) async {
    final db = await database;
    return await db.insert('questions', question.toMap());
  }

  Future<List<Question>> getQuestionsByExam(int examId) async {
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'questions',
      where: 'exam_id = ?',
      whereArgs: [examId],
    );
    return List.generate(maps.length, (i) => Question.fromMap(maps[i]));
  }

  // Submission methods
  Future<int> insertSubmission(Submission submission) async {
    final db = await database;
    return await db.insert('submissions', submission.toMap());
  }

  Future<List<Submission>> getSubmissionsByExam(int examId) async {
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'submissions',
      where: 'exam_id = ?',
      whereArgs: [examId],
    );
    return List.generate(maps.length, (i) => Submission.fromMap(maps[i]));
  }

  Future<List<Map<String, dynamic>>> getExamResults() async {
    final db = await database;
    return await db.query('exam_results', orderBy: 'submitted_at DESC');
  }
}
