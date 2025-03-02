import 'package:flutter/foundation.dart';
import '../models/exam.dart';
import '../models/question.dart';
import '../services/database_service.dart';
import '../services/api_service.dart';

class ExamProvider extends ChangeNotifier {
  final DatabaseService _dbService;
  final ApiService _apiService;

  Exam? _currentExam;
  List<Question> _questions = [];
  bool _isLoading = false;
  String? _error;

  ExamProvider({
    required DatabaseService dbService,
    required ApiService apiService,
  })  : _dbService = dbService,
        _apiService = apiService;

  Exam? get currentExam => _currentExam;
  List<Question> get questions => _questions;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadExam(int examId) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      // Thử tải từ local database trước
      _currentExam = await _dbService.getExam(examId);
      _questions = await _dbService.getQuestionsByExam(examId);

      // Nếu không có dữ liệu local, tải từ server
      if (_currentExam == null) {
        final examData = await _apiService.fetchExamData(examId);

        // Lưu exam vào database
        _currentExam = Exam.fromMap(examData['exam']);
        await _dbService.insertExam(_currentExam!);

        // Lưu questions vào database
        final questionsList = examData['questions'] as List;
        _questions = questionsList.map((q) => Question.fromMap(q)).toList();
        for (var question in _questions) {
          await _dbService.insertQuestion(question);
        }
      }

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      print('Error loading exam: $e');
    }
  }
}
