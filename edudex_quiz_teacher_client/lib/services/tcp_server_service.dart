import 'dart:io';
import 'dart:convert';
import '../models/student.dart';
import '../models/exam.dart';
import '../models/question.dart';
import '../models/submission.dart';
import '../services/database_service.dart';
import '../constants/message_types.dart';
import 'package:network_info_plus/network_info_plus.dart';
import '../services/log_service.dart';

class TcpServerService {
  ServerSocket? _server;
  final Map<String, Socket> _connectedClients = {};
  final DatabaseService _dbService;
  final LogService _logService;
  static const int DEFAULT_PORT = 8689;
  String? _localIp;

  // Callback để cập nhật số lượng client trong provider
  Function(int)? onClientCountChanged;

  TcpServerService({
    required DatabaseService dbService,
    required LogService logService,
    this.onClientCountChanged,
  })  : _dbService = dbService,
        _logService = logService;

  Future<String?> get localIp async {
    if (_localIp != null) return _localIp;

    try {
      final info = NetworkInfo();
      _localIp = await info.getWifiIP();
      return _localIp;
    } catch (e) {
      _logService.log('Error getting local IP: $e');
      return null;
    }
  }

  Future<void> startServer({int port = DEFAULT_PORT}) async {
    try {
      _server = await ServerSocket.bind(InternetAddress.anyIPv4, port);
      _logService.log('TCP Server listening on port $port');
      final ip = await localIp;
      if (ip != null) {
        _logService.log('Teacher IP: $ip');
      }

      _server!.listen((Socket client) {
        _logService.log(
            'Client connected: ${client.remoteAddress.address}:${client.remotePort}');

        String buffer = '';
        client.listen(
          (List<int> data) {
            try {
              buffer += utf8.decode(data);

              while (buffer.contains('\n')) {
                final parts = buffer.split('\n');
                final message = parts[0];
                buffer = parts.sublist(1).join('\n');

                if (message.isNotEmpty) {
                  try {
                    final jsonData = json.decode(message);
                    _handleClientMessage(client, jsonData);
                  } catch (e) {
                    _logService.log('Error parsing JSON: $e',
                        level: LogLevel.error);
                    _sendError(client, 'Invalid JSON format');
                  }
                }
              }
            } catch (e) {
              _logService.log('Error processing message: $e',
                  level: LogLevel.error);
              _sendError(client, 'Error processing message');
            }
          },
          onError: (error) {
            _logService.log('Error from client: $error', level: LogLevel.error);
            _removeClient(client);
          },
          onDone: () {
            _logService
                .log('Client disconnected: ${client.remoteAddress.address}');
            _removeClient(client);
          },
        );
      });
    } catch (e) {
      _logService.log('Error starting server: $e', level: LogLevel.error);
      rethrow;
    }
  }

  void _handleClientMessage(Socket client, Map<String, dynamic> data) {
    try {
      final messageType = data['type'];
      _logService.log('📥 Nhận message type: $messageType, Data: $data');

      switch (messageType) {
        case MessageTypes.CHECK_TEACHER:
          final clientId =
              '${client.remoteAddress.address}:${client.remotePort}';
          _logService.log('🔄 Xử lý CHECK_TEACHER từ client: $clientId');

          final response = {
            'type': 'TEACHER_OK',
            'timestamp': DateTime.now().toIso8601String()
          };
          _logService.log('📤 Gửi response: $response');
          _sendMessage(client, response);

          _connectedClients[clientId] = client;
          onClientCountChanged?.call(_connectedClients.length);
          _logService.log('✅ Đã xác thực student: $clientId');
          break;

        case MessageTypes.LOGIN:
          _handleLogin(client, data);
          break;

        case MessageTypes.SUBMIT_ANSWER:
          _handleSubmitAnswer(client, data);
          break;

        default:
          _sendError(client, 'Unknown message type: $messageType');
      }
    } catch (e) {
      _logService.log('❌ Lỗi xử lý message: $e', level: LogLevel.error);
      _sendError(client, e.toString());
    }
  }

  Future<void> _handleLogin(Socket client, Map<String, dynamic> data) async {
    try {
      final String studentCode = data['student_code'];
      final int examId = data['exam_id'];

      // Kiểm tra thông tin đăng nhập
      final students = await _dbService.getStudentsByExam(examId);
      final student = students.firstWhere(
        (s) => s.studentCode == studentCode,
        orElse: () => throw Exception('Invalid student code'),
      );

      if (student.status != 'not_started') {
        throw Exception('Student has already started or completed the exam');
      }

      // Lưu socket connection cho student
      _connectedClients[studentCode] = client;

      // Gửi phản hồi thành công
      _sendMessage(client, {
        'type': MessageTypes.LOGIN_RESPONSE,
        'success': true,
        'student_name': student.name,
      });

      _logService.log('Student logged in: $studentCode');
    } catch (e) {
      _sendMessage(client, {
        'type': MessageTypes.LOGIN_RESPONSE,
        'success': false,
        'error': e.toString(),
      });
    }
  }

  Future<void> _handleStartExam(
      Socket client, Map<String, dynamic> data) async {
    try {
      final String studentCode = data['student_code'];
      final int examId = data['exam_id'];

      // Cập nhật trạng thái student
      final students = await _dbService.getStudentsByExam(examId);
      final student = students.firstWhere((s) => s.studentCode == studentCode);
      await _dbService.updateStudentStatus(student.id, 'in_progress');

      // Lấy thông tin đề thi
      final exam = await _dbService.getExam(examId);
      final questions = await _dbService.getQuestionsByExam(examId);

      // Gửi đề thi cho student
      _sendMessage(client, {
        'type': MessageTypes.EXAM_DATA,
        'exam': exam?.toMap(),
        'questions': questions.map((q) => q.toMap()).toList(),
      });

      _logService.log('Exam started for student: $studentCode');
    } catch (e) {
      _sendError(client, e.toString());
    }
  }

  Future<void> _handleSubmitAnswer(
      Socket client, Map<String, dynamic> data) async {
    try {
      final String studentCode = data['student_code'];
      final int examId = data['exam_id'];
      final Map<String, String> answers =
          Map<String, String>.from(data['answers']);

      // Lấy thông tin student và câu hỏi
      final students = await _dbService.getStudentsByExam(examId);
      final student = students.firstWhere((s) => s.studentCode == studentCode);
      final questions = await _dbService.getQuestionsByExam(examId);

      // Tính điểm
      int correctCount = 0;
      for (var question in questions) {
        if (answers[question.id.toString()] == question.correctAnswer) {
          correctCount++;
        }
      }
      final double score = (correctCount / questions.length) * 10;

      // Lưu kết quả
      await _dbService.insertSubmission(
        Submission(
          id: 0, // Auto-increment
          studentId: student.id,
          examId: examId,
          answers: answers,
          score: score,
          correctCount: correctCount,
          submittedAt: DateTime.now(),
        ),
      );

      // Cập nhật trạng thái student
      await _dbService.updateStudentStatus(student.id, 'completed');

      // Gửi kết quả cho student
      _sendMessage(client, {
        'type': MessageTypes.SUBMIT_RESPONSE,
        'score': score,
        'correct_count': correctCount,
        'total_questions': questions.length,
      });

      _logService
          .log('Exam submitted for student: $studentCode, Score: $score');
    } catch (e) {
      _sendError(client, e.toString());
    }
  }

  void _sendMessage(Socket client, Map<String, dynamic> message) {
    try {
      final jsonStr = json.encode(message);
      client.write('$jsonStr\n');
    } catch (e) {
      _logService.log('Error sending message: $e', level: LogLevel.error);
    }
  }

  void _sendError(Socket client, String error) {
    _sendMessage(client, {
      'type': MessageTypes.ERROR,
      'message': error,
    });
  }

  void _removeClient(Socket client) {
    String? clientId;
    _connectedClients.forEach((id, socket) {
      if (socket == client) clientId = id;
    });
    if (clientId != null) {
      _connectedClients.remove(clientId);
      onClientCountChanged?.call(_connectedClients.length);
      _logService.log('Removed client: $clientId');
      client.close();
    }
  }

  void stopServer() {
    for (var client in _connectedClients.values) {
      client.close();
    }
    _connectedClients.clear();
    _server?.close();
    onClientCountChanged?.call(0);
    _logService.log('TCP Server stopped');
  }
}
