import 'package:flutter/foundation.dart';
import '../models/student.dart';
import '../services/database_service.dart';

class StudentProvider extends ChangeNotifier {
  final DatabaseService _dbService;
  List<Student> _students = [];
  bool _isLoading = false;
  String? _error;

  StudentProvider({required DatabaseService dbService})
      : _dbService = dbService;

  List<Student> get students => _students;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadStudents(int examId) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      _students = await _dbService.getStudentsByExam(examId);

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      print('Error loading students: $e');
    }
  }

  Future<void> updateStudentStatus(int studentId, String status) async {
    try {
      await _dbService.updateStudentStatus(studentId, status);

      // Cập nhật status trong list local
      final index = _students.indexWhere((s) => s.id == studentId);
      if (index != -1) {
        _students[index].status = status;
        notifyListeners();
      }
    } catch (e) {
      print('Error updating student status: $e');
    }
  }
}
