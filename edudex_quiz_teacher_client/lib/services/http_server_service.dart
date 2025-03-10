import 'dart:io';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:async';
import './exam_database_service.dart';
import 'package:intl/intl.dart';

class HttpServerService {
  static final HttpServerService _instance = HttpServerService._internal();
  factory HttpServerService() => _instance;
  HttpServerService._internal();

  HttpServer? _server;
  bool _isRunning = false;
  String? _teacherIp;
  final Map<int, String> _connectedComputers = {};
  final Map<int, DateTime> _connectionTimes = {};
  final Map<int, bool> _connectionStatus =
      {}; // true = connected, false = disconnected
  Map<int, bool> get connectionStatus => _connectionStatus;

  Timer? _heartbeatTimer;

  final Map<int, String> _studentNames = {};
  final Map<int, String> _studentExamCodes = {};
  final Map<int, String> _studentCodes = {};
  Map<int, String> get studentNames => _studentNames;
  Map<int, String> get studentExamCodes => _studentExamCodes;
  Map<int, String> get studentCodes => _studentCodes;

  bool get isRunning => _isRunning;
  String? get teacherIp => _teacherIp;
  Map<int, String> get connectedComputers => _connectedComputers;
  Map<int, DateTime> get connectionTimes => _connectionTimes;

  String _getDefaultStartIp(String currentIp) {
    try {
      final parts = currentIp.split('.');
      parts[3] = '100'; // Đặt octet cuối thành 100
      return parts.join('.');
    } catch (e) {
      return '192.168.1.1';
    }
  }

  String _getDefaultEndIp(String currentIp) {
    try {
      final parts = currentIp.split('.');
      parts[3] = '150'; // Đặt octet cuối thành 150
      return parts.join('.');
    } catch (e) {
      return '192.168.1.255';
    }
  }

  Future<void> startServer() async {
    if (_isRunning) return;

    try {
      _connectedComputers.clear();
      _connectionTimes.clear();
      _connectionStatus.clear();

      _server = await HttpServer.bind(InternetAddress.anyIPv4, 8689);
      _isRunning = true;

      // Lấy IP của teacher
      final interfaces = await NetworkInterface.list();
      _teacherIp = interfaces
          .expand((interface) => interface.addresses)
          .firstWhere(
            (addr) => addr.type == InternetAddressType.IPv4 && !addr.isLoopback,
            orElse: () => InternetAddress('0.0.0.0'),
          )
          .address;

      // Lưu dải IP mặc định nếu chưa có
      final prefs = await SharedPreferences.getInstance();
      if (!prefs.containsKey('server_start_ip')) {
        await prefs.setString(
          'server_start_ip',
          _getDefaultStartIp(_teacherIp!),
        );
      }
      if (!prefs.containsKey('server_end_ip')) {
        await prefs.setString(
          'server_end_ip',
          _getDefaultEndIp(_teacherIp!),
        );
      }

      // Một listener duy nhất cho tất cả requests
      _server!.listen((HttpRequest request) async {
        // Thêm CORS headers cho mọi request
        request.response.headers.add('Access-Control-Allow-Origin', '*');
        request.response.headers
            .add('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        request.response.headers
            .add('Access-Control-Allow-Headers', 'Origin, Content-Type');
        request.response.headers.contentType = ContentType.json;

        // Xử lý OPTIONS request
        if (request.method == 'OPTIONS') {
          request.response.statusCode = HttpStatus.ok;
          await request.response.close();
          return;
        }

        // Xử lý các endpoints
        switch (request.uri.path) {
          case '/auth/student':
            if (request.method == 'POST') {
              await handleStudentAuth(request);
            }
            break;

          case '/is-teacher': // Thêm endpoint is-teacher
            await handleComputerRegistration(request);
            break;

          case '/exam-info':
            await _handleRequest(request);
            break;

          case '/exam-questions': // Thêm case mới
            if (request.method == 'GET') {
              await handleExamQuestions(request);
            }
            break;

          case '/exam-submissions':
            if (request.method == 'POST') {
              await handleExamSubmission(request);
            }
            break;

          default:
            request.response.statusCode = HttpStatus.notFound;
            request.response.write(json.encode({
              'status': 'error',
              'message': 'Không tìm thấy endpoint',
            }));
            await request.response.close();
        }
      });

      // Khởi động timer kiểm tra kết nối
      _heartbeatTimer?.cancel();
      _heartbeatTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
        _checkAllConnections();
      });
    } catch (e) {
      print('Lỗi khởi động server: $e');
      _isRunning = false;
    }
  }

  Future<void> stopServer() async {
    if (!_isRunning) return;
    _heartbeatTimer?.cancel();
    _connectionStatus.clear();
    _connectedComputers.clear();
    _connectionTimes.clear();
    await _server?.close();
    _server = null;
    _isRunning = false;
    _teacherIp = null;
  }

  Future<bool> _isAllowedIp(String ip) async {
    final prefs = await SharedPreferences.getInstance();
    final startIp = prefs.getString('server_start_ip') ??
        _getDefaultStartIp(_teacherIp ?? '192.168.1.1');
    final endIp = prefs.getString('server_end_ip') ??
        _getDefaultEndIp(_teacherIp ?? '192.168.1.1');
    final blockedIpsStr = prefs.getString('server_blocked_ips') ?? '';

    // Kiểm tra IP bị chặn
    final blockedIps = blockedIpsStr
        .split(',')
        .map((e) => e.trim())
        .where((e) => e.isNotEmpty)
        .toList();
    if (blockedIps.contains(ip)) return false;

    // Kiểm tra IP trong dải cho phép
    try {
      final ipNum = _ipToNum(ip);
      final startNum = _ipToNum(startIp);
      final endNum = _ipToNum(endIp);
      return ipNum >= startNum && ipNum <= endNum;
    } catch (e) {
      return false;
    }
  }

  int _ipToNum(String ip) {
    final parts = ip.split('.').map((part) => int.parse(part)).toList();
    return (parts[0] << 24) + (parts[1] << 16) + (parts[2] << 8) + parts[3];
  }

  void disconnectComputer(int computerNumber) {
    _connectedComputers.remove(computerNumber);
    _connectionTimes.remove(computerNumber);
    _connectionStatus.remove(computerNumber);
  }

  Future<void> _checkAllConnections() async {
    for (final entry in _connectedComputers.entries) {
      final computerNumber = entry.key;
      final ip = entry.value;

      try {
        final response = await http
            .get(
              Uri.parse('http://$ip:8688/heart-beat'),
            )
            .timeout(const Duration(seconds: 5));

        if (response.statusCode == 200) {
          _connectionStatus[computerNumber] = true;
        } else {
          _connectionStatus[computerNumber] = false;
        }
      } catch (e) {
        _connectionStatus[computerNumber] = false;
      }
    }
  }

  // Thêm method kiểm tra IP đã kết nối với máy nào chưa
  int? _getConnectedComputer(String ip) {
    for (var entry in _connectedComputers.entries) {
      if (entry.value == ip) {
        return entry.key;
      }
    }
    return null;
  }

  // Tách logic xử lý student auth thành method riêng
  Future<void> handleStudentAuth(HttpRequest request) async {
    try {
      final queryParams = request.uri.queryParameters;
      final examCode = queryParams['exam_code'];
      final studentCode = queryParams['student_code']?.toUpperCase();

      // Kiểm tra tham số
      if (examCode == null || studentCode == null) {
        request.response.statusCode = HttpStatus.badRequest;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Thiếu thông tin đăng nhập',
        }));
        return;
      }

      // Lấy IP của client request
      final clientIp = request.connectionInfo?.remoteAddress.address;

      // Kiểm tra xem IP này đã được xác thực chưa
      final mayId = _connectedComputers.entries
          .firstWhere((entry) => entry.value == clientIp,
              orElse: () => const MapEntry(0, ''))
          .key;

      if (mayId == 0) {
        request.response.statusCode = HttpStatus.unauthorized;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Máy chưa được xác thực',
        }));
        return;
      }

      // Kiểm tra thông tin sinh viên
      final examDb = ExamDatabaseService();
      final student = await examDb.getStudentByExamCode(examCode, studentCode);

      if (student == null) {
        request.response.statusCode = HttpStatus.unauthorized;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Số báo danh hoặc mã sinh viên không đúng',
        }));
        return;
      }

      // Tạo token mới
      final token = base64Encode(utf8.encode(
          'copecute${student['exam_code']}${DateTime.now().day}${DateTime.now().month}${student['student_code']}${DateTime.now().hour}${DateTime.now().minute}${DateTime.now().second}'));

      // Cập nhật token mới vào database
      await examDb.updateStudentToken(student['id'], token);

      // Lưu thông tin sinh viên vào maps để hiển thị
      final computerNumber = _getConnectedComputer(clientIp!) ?? 0;
      _studentNames[computerNumber] = student['full_name'] ?? '';
      _studentExamCodes[computerNumber] = student['exam_code'] ?? '';
      _studentCodes[computerNumber] = student['student_code'] ?? '';

      // Trả về response thành công
      request.response.write(json.encode({
        'status': 'success',
        'message': 'Đăng nhập thành công',
        'data': {
          'token': token,
          'student': {
            'id': student['id'],
            'exam_code': student['exam_code'],
            'student_code': student['student_code'],
            'full_name': student['full_name'],
            'date_of_birth': student['date_of_birth'],
            'gender': student['gender'],
            'phone': student['phone'],
            'seat_number': student['seat_number'],
            'address': student['address'],
          },
        },
      }));
    } catch (e) {
      print('Lỗi xử lý đăng nhập: $e');
      request.response.statusCode = HttpStatus.internalServerError;
      request.response.write(json.encode({
        'status': 'error',
        'message': 'Lỗi server: $e',
      }));
    } finally {
      await request.response.close();
    }
  }

  // Tách logic xử lý computer registration thành method riêng
  Future<void> handleComputerRegistration(HttpRequest request) async {
    try {
      // Kiểm tra IP bị cấm
      final clientIp = request.connectionInfo?.remoteAddress.address;
      if (clientIp != null && !await _isAllowedIp(clientIp)) {
        request.response.statusCode = HttpStatus.forbidden;
        request.response.write(json.encode({
          'status': 'error',
          'messages': 'IP không được phép truy cập',
        }));
        await request.response.close();
        return;
      }

      if (request.method == 'GET' && request.uri.path == '/is-teacher') {
        request.response.write(json.encode({
          'status': 'success',
          'messages': 'đây là teacher',
        }));
      } else if (request.method == 'POST' &&
          request.uri.path == '/is-teacher') {
        try {
          if (!request.uri.hasQuery) {
            request.response.statusCode = HttpStatus.badRequest;
            request.response.write(json.encode({
              'status': 'error',
              'messages': 'Dữ liệu phải được gửi qua query params',
            }));
            await request.response.close();
            return;
          }

          final data = request.uri.queryParameters;
          if (data['may'] == null) {
            request.response.statusCode = HttpStatus.badRequest;
            request.response.write(json.encode({
              'status': 'error',
              'messages': 'Thiếu thông tin máy',
            }));
            return;
          }

          final mayId = int.tryParse(data['may']!);
          if (mayId == null) {
            request.response.statusCode = HttpStatus.badRequest;
            request.response.write(json.encode({
              'status': 'error',
              'messages': 'Số máy không hợp lệ',
            }));
            return;
          }

          // Kiểm tra IP đã kết nối máy khác chưa
          final existingComputer = _getConnectedComputer(clientIp!);
          if (existingComputer != null && existingComputer != mayId) {
            request.response.statusCode = HttpStatus.conflict;
            request.response.write(json.encode({
              'status': 'error',
              'messages': 'IP này đã kết nối với máy $existingComputer',
            }));
            return;
          }

          // Lấy số máy tối đa từ SharedPreferences
          final prefs = await SharedPreferences.getInstance();
          final roomCapacity = prefs.getInt('room_capacity');

          // Kiểm tra số máy có hợp lệ không
          if (roomCapacity == null) {
            request.response.statusCode = HttpStatus.badRequest;
            request.response.write(json.encode({
              'status': 'error',
              'messages': 'Chưa chọn phòng thi',
            }));
            return;
          }

          if (mayId <= 0 || mayId > roomCapacity) {
            request.response.statusCode = HttpStatus.badRequest;
            request.response.write(json.encode({
              'status': 'error',
              'messages': 'Số máy không tồn tại trong phòng thi',
            }));
            return;
          }

          _connectedComputers[mayId] = clientIp!;
          _connectionTimes[mayId] = DateTime.now();
          _connectionStatus[mayId] = true;
          request.response.write(json.encode({
            'status': 'success',
            'messages': 'Đã xác nhận máy $mayId',
          }));
        } catch (e) {
          request.response.statusCode = HttpStatus.badRequest;
          request.response.write(json.encode({
            'status': 'error',
            'messages': 'Dữ liệu không hợp lệ',
          }));
        }
      } else {
        request.response.statusCode = HttpStatus.notFound;
        request.response.write(json.encode({
          'status': 'error',
          'messages': 'Không tìm thấy endpoint',
        }));
      }

      await request.response.close();
    } catch (e) {
      print('Lỗi xử lý đăng nhập: $e');
      request.response.statusCode = HttpStatus.internalServerError;
      request.response.write(json.encode({
        'status': 'error',
        'message': 'Lỗi server: $e',
      }));
      await request.response.close();
    }
  }

  Future<void> _handleRequest(HttpRequest request) async {
    request.response.headers.contentType = ContentType.json;

    try {
      if (request.uri.path == '/exam-info') {
        // Kiểm tra header Authorization
        final authHeader = request.headers.value('Authorization');
        if (authHeader == null || !authHeader.startsWith('copecute ')) {
          request.response.statusCode = HttpStatus.unauthorized;
          request.response.write(json.encode({
            'status': 'error',
            'message': 'Không có token xác thực',
          }));
          return;
        }

        final token = authHeader.substring('copecute '.length);

        // Kiểm tra token trong database
        final examDb = ExamDatabaseService();
        final student = await examDb.getStudentByToken(token);

        if (student == null) {
          request.response.statusCode = HttpStatus.unauthorized;
          request.response.write(json.encode({
            'status': 'error',
            'message': 'Token không hợp lệ hoặc đã hết hạn',
          }));
          return;
        }

        // Lấy thông tin đề thi và thông tin phiên thi từ database
        final examData = await examDb.getExamData();
        final sessionInfo = await examDb.getSessionInfo();

        print('📝 Exam data: $examData');
        print('📝 Session info: $sessionInfo');

        if (examData == null) {
          request.response.statusCode = HttpStatus.notFound;
          request.response.write(json.encode({
            'status': 'error',
            'message': 'Không tìm thấy thông tin đề thi',
          }));
          return;
        }

        // Trả về thông tin đề thi với thông tin phiên thi từ database
        request.response.write(json.encode({
          'status': 'success',
          'message': 'Lấy thông tin đề thi thành công',
          'data': {
            'subject': {
              'name': examData['subject']['name'] ?? 'Chưa có tên môn học',
              'code': examData['subject']['code'] ?? '',
            },
            'exam': {
              'name': examData['exam']['name'] ?? 'Chưa có tên đề thi',
              'duration': examData['exam']['duration'] ?? 0,
              'total_questions': examData['exam']['total_questions'] ?? 0,
              'description': examData['exam']['description'] ?? '',
            },
            'shift': sessionInfo?['shift'] ??
                {
                  'name': 'Ca 1',
                  'start_time': DateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSSSS")
                      .format(DateTime.now().toLocal()),
                  'end_time': DateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSSSS").format(
                      DateTime.now().toLocal().add(const Duration(hours: 2))),
                },
            'room': {
              'name': sessionInfo?['room']?['name'] ?? 'Phòng máy 1',
              'facility': sessionInfo?['room']?['location'] ?? 'Tầng 1',
            },
            'test_session': sessionInfo?['test_session'] ??
                {
                  'name': 'Kỳ thi mặc định',
                  'start_date': DateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSSSS")
                      .format(DateTime.now().toLocal()),
                  'end_date': DateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSSSS").format(
                      DateTime.now().toLocal().add(const Duration(days: 7))),
                }
          }
        }));
      } else {
        request.response.statusCode = HttpStatus.notFound;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Không tìm thấy endpoint',
        }));
      }

      await request.response.close();
    } catch (e) {
      print('❌ Lỗi xử lý request: $e');
      request.response.statusCode = HttpStatus.internalServerError;
      request.response.write(json.encode({
        'status': 'error',
        'message': 'Lỗi server: $e',
      }));
      await request.response.close();
    }
  }

  // Thêm method xử lý exam questions
  Future<void> handleExamQuestions(HttpRequest request) async {
    try {
      // Kiểm tra header Authorization
      final authHeader = request.headers.value('Authorization');
      if (authHeader == null || !authHeader.startsWith('copecute ')) {
        request.response.statusCode = HttpStatus.unauthorized;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Không có token xác thực',
        }));
        return;
      }

      final token = authHeader.substring('copecute '.length);

      // Kiểm tra token trong database
      final examDb = ExamDatabaseService();
      final student = await examDb.getStudentByToken(token);

      if (student == null) {
        request.response.statusCode = HttpStatus.unauthorized;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Token không hợp lệ hoặc đã hết hạn',
        }));
        return;
      }

      // Lấy thông tin đề thi
      final examData = await examDb.getExamData();
      if (examData == null) {
        request.response.statusCode = HttpStatus.notFound;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Không tìm thấy thông tin đề thi',
        }));
        return;
      }

      // Lấy danh sách câu hỏi và random
      List<Map<String, dynamic>> questions =
          examData['questions'].cast<Map<String, dynamic>>();
      questions.shuffle(); // Random thứ tự câu hỏi

      // Xử lý từng câu hỏi
      final processedQuestions = questions.map((q) {
        // Random thứ tự đáp án
        List<Map<String, dynamic>> answers = List.from(q['answers']);
        answers.shuffle();

        // Loại bỏ thông tin đáp án đúng
        answers = answers
            .map((a) => {
                  'id': a['id'],
                  'content': a['content'],
                  'media': a['media'],
                })
            .toList();

        return {
          'id': q['id'],
          'content': q['content'],
          'type': q['type'],
          'media': q['media'],
          'tags': q['tags'],
          'answers': answers,
        };
      }).toList();

      // Trả về response
      request.response.write(json.encode({
        'status': 'success',
        'message': 'Lấy danh sách câu hỏi thành công',
        'data': {
          'questions': processedQuestions,
        }
      }));
    } catch (e) {
      print('❌ Lỗi xử lý exam questions: $e');
      request.response.statusCode = HttpStatus.internalServerError;
      request.response.write(json.encode({
        'status': 'error',
        'message': 'Lỗi server: $e',
      }));
    } finally {
      await request.response.close();
    }
  }

  Future<void> handleExamSubmission(HttpRequest request) async {
    try {
      // Kiểm tra header Authorization
      final authHeader = request.headers.value('Authorization');
      if (authHeader == null || !authHeader.startsWith('copecute ')) {
        request.response.statusCode = HttpStatus.unauthorized;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Không có token xác thực',
        }));
        await request.response.close();
        return;
      }

      final token = authHeader.substring('copecute '.length);
      print('Xử lý nộp bài:');
      print('Token: $token');

      // Kiểm tra token trong database
      final examDb = ExamDatabaseService();
      final student = await examDb.getStudentByToken(token);
      print('Student: ${json.encode(student)}');

      if (student == null) {
        request.response.statusCode = HttpStatus.unauthorized;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Token không hợp lệ hoặc đã hết hạn',
        }));
        await request.response.close();
        return;
      }

      // Kiểm tra đã nộp bài chưa
      final hasSubmitted = await examDb.hasSubmittedExam(
        student['exam_code'],
        student['student_code'],
      );

      if (hasSubmitted) {
        request.response.statusCode = HttpStatus.badRequest;
        request.response.write(json.encode({
          'status': 'error',
          'message': 'Bạn đã nộp bài trước đó không thể nộp lại',
        }));
        await request.response.close();
        return;
      }

      // Parse request body
      final body = await utf8.decoder.bind(request).join();
      final data = json.decode(body);
      print('Request data: ${json.encode(data)}');

      // Kiểm tra kết quả
      int totalCorrect = 0;
      final answers = List<Map<String, dynamic>>.from(data['answers']);

      // Lấy câu hỏi và đáp án đúng
      final examData = await examDb.getExamData();
      if (examData == null || examData['questions'] == null) {
        throw Exception('Không tìm thấy thông tin đề thi');
      }

      final questions = List<Map<String, dynamic>>.from(examData['questions']);
      print('Questions count: ${questions.length}');

      // Tạo map câu hỏi -> đáp án đúng
      final correctAnswers = <int, int>{};
      for (final q in questions) {
        try {
          final answers = List<Map<String, dynamic>>.from(q['answers']);
          final correctAnswer = answers.firstWhere(
            (a) => a['is_correct'] == true,
            orElse: () => <String, dynamic>{},
          );

          if (correctAnswer.isNotEmpty && correctAnswer['id'] != null) {
            // Lưu theo dạng question_id -> correct_answer_id
            correctAnswers[q['id']] = correctAnswer['id'];
          }
        } catch (e) {
          print(
              'Warning: Không tìm thấy đáp án đúng cho câu hỏi ${q['id']}: $e');
          continue;
        }
      }
      print('Correct answers map: $correctAnswers');

      // Kiểm tra từng câu trả lời
      for (final answer in answers) {
        final questionId = answer['question_id'];
        final answerId = answer['answer_id'];

        // So sánh answer_id với correct_answer_id
        if (correctAnswers.containsKey(questionId) &&
            correctAnswers[questionId] == answerId) {
          totalCorrect++;
          print('✅ Câu $questionId đúng (answer: $answerId)');
        } else {
          print(
              '❌ Câu $questionId sai (answer: $answerId, correct: ${correctAnswers[questionId]})');
        }
      }

      // Tính điểm
      final totalQuestions = questions.length;
      final score = double.parse(
          ((totalCorrect / totalQuestions) * 10).toStringAsFixed(1));

      print('Score calculation:');
      print('Total correct: $totalCorrect');
      print('Total questions: $totalQuestions');
      print('Score: $score');

      // Lưu kết quả
      await examDb.saveExamResult(
        examCode: student['exam_code'],
        studentCode: student['student_code'],
        correctAnswers: totalCorrect,
        totalQuestions: totalQuestions,
        score: score,
        logFile: data['submission_file'],
        note: null,
        submittedAt: DateTime.now(),
        fullName: student['full_name'],
      );

      // Trả về kết quả
      request.response.write(json.encode({
        'status': 'success',
        'message': 'Nộp bài thành công',
        'data': {
          'total_questions': totalQuestions,
          'correct_answers': totalCorrect,
          'score': score,
        }
      }));
    } catch (e, stackTrace) {
      print('❌ Lỗi xử lý nộp bài:');
      print('Error: $e');
      print('Stack trace: $stackTrace');
      request.response.statusCode = HttpStatus.internalServerError;
      request.response.write(json.encode({
        'status': 'error',
        'message': 'Lỗi server: $e',
      }));
    } finally {
      await request.response.close();
    }
  }
}
