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
import '../models/room_settings.dart';
import 'package:shared_preferences/shared_preferences.dart';

class TcpServerService {
  ServerSocket? _server;
  final Map<String, Socket> _connectedClients = {};
  final DatabaseService _dbService;
  final LogService _logService;
  static const int DEFAULT_PORT = 8689;
  String? _localIp;
  late RoomSettings _settings;

  // Callback để cập nhật số lượng client trong provider
  Function(int)? onClientCountChanged;

  TcpServerService({
    required DatabaseService dbService,
    required LogService logService,
    this.onClientCountChanged,
  })  : _dbService = dbService,
        _logService = logService {
    _initSettings();
  }

  Future<void> _initSettings() async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // Load settings từ SharedPreferences, nếu không có thì dùng giá trị mặc định
      _settings = RoomSettings(
        startIp: prefs.getString('room_start_ip') ?? '192.168.0.10',
        endIp: prefs.getString('room_end_ip') ?? '192.168.0.200',
        blockedIps: prefs.getStringList('room_blocked_ips') ?? [],
        maxComputers: prefs.getInt('room_max_computers') ?? 50,
      );

      _logService.log('✅ Đã load cài đặt phòng thi: $_settings');
    } catch (e) {
      // Nếu có lỗi thì dùng giá trị mặc định
      _settings = RoomSettings(
        startIp: '192.168.0.10',
        endIp: '192.168.0.200',
        blockedIps: [],
        maxComputers: 50,
      );
      _logService.log('❌ Lỗi load cài đặt, dùng mặc định: $e',
          level: LogLevel.error);
    }
  }

  Future<void> updateSettings(RoomSettings settings) async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // Lưu settings mới vào SharedPreferences
      await prefs.setString('room_start_ip', settings.startIp);
      await prefs.setString('room_end_ip', settings.endIp);
      await prefs.setStringList('room_blocked_ips', settings.blockedIps);
      await prefs.setInt('room_max_computers', settings.maxComputers);

      // Cập nhật settings hiện tại
      _settings = settings;
      _logService.log('✅ Đã lưu cài đặt phòng thi: $_settings');
    } catch (e) {
      _logService.log('❌ Lỗi lưu cài đặt: $e', level: LogLevel.error);
      rethrow;
    }
  }

  RoomSettings get settings => _settings;

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
        final clientIp = client.remoteAddress.address;
        final clientId = '$clientIp:${client.remotePort}';

        // Kiểm tra IP có được phép không
        if (!_settings.isIpAllowed(clientIp)) {
          _logService.log('❌ Từ chối kết nối từ IP không được phép: $clientIp');
          client.close();
          return;
        }

        // Kiểm tra số lượng máy
        final connectedIps =
            _connectedClients.keys.map((id) => id.split(':')[0]).toSet();
        if (connectedIps.length >= _settings.maxComputers) {
          _logService.log('❌ Từ chối kết nối: Đã đạt số lượng máy tối đa');
          client.close();
          return;
        }

        _logService.log('📥 Nhận kết nối từ: $clientId');

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
                    _logService.log('❌ Lỗi parse JSON: $e',
                        level: LogLevel.error);
                  }
                }
              }
            } catch (e) {
              _logService.log('❌ Lỗi xử lý dữ liệu: $e', level: LogLevel.error);
            }
          },
          onError: (error) {
            _logService.log('❌ Lỗi kết nối: $error', level: LogLevel.error);
          },
          onDone: () {
            _logService
                .log('Client ${client.remoteAddress.address} ngắt kết nối');
          },
          cancelOnError: false,
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
      final clientId = '${client.remoteAddress.address}:${client.remotePort}';
      _logService.log('📥 Nhận message type: $messageType, Data: $data');

      switch (messageType) {
        case MessageTypes.CHECK_TEACHER:
          _logService.log('🔄 Xử lý CHECK_TEACHER từ client: $clientId');

          // Thêm vào danh sách sau khi xác thực thành công
          _connectedClients[clientId] = client;
          onClientCountChanged?.call(_connectedClients.length);

          // Gửi response và giữ kết nối
          final response = {
            'type': 'TEACHER_OK',
            'timestamp': DateTime.now().toIso8601String()
          };
          _sendMessage(client, response);
          _logService.log('✅ Đã xác thực student: $clientId');
          break;

        case 'HEARTBEAT':
          // Không cần làm gì, chỉ để duy trì kết nối
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

  void disconnectClient(String clientId) {
    final client = _connectedClients[clientId];
    if (client != null) {
      client.close();
      _connectedClients.remove(clientId);
      onClientCountChanged?.call(_connectedClients.length);
      _logService.log('❌ Đã ngắt kết nối client: $clientId');
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

  Map<String, Socket> get connectedClients => _connectedClients;

  bool get isRunning => _server != null;
}
