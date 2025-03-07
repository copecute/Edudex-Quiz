import 'dart:io';
import 'dart:async';
import 'dart:convert';
import 'package:shelf/shelf.dart' as shelf;
import 'package:shelf/shelf_io.dart' as shelf_io;
import 'package:http/http.dart' as http;

class ConnectionService {
  static final ConnectionService _instance = ConnectionService._internal();
  factory ConnectionService() => _instance;
  ConnectionService._internal();

  static const int STUDENT_PORT = 8688;
  static const int TEACHER_PORT = 8689;

  HttpServer? _server;
  String? _teacherIP;
  final _messageController = StreamController<Map<String, dynamic>>.broadcast();
  Stream<Map<String, dynamic>> get messageStream => _messageController.stream;

  bool get isConnected => _teacherIP != null;

  Future<void> startServer({int port = STUDENT_PORT}) async {
    try {
      // Tạo handler cho HTTP server
      final handler = const shelf.Pipeline()
          .addMiddleware(shelf.logRequests())
          .addHandler(_handleRequest);

      // Khởi tạo HTTP server
      _server = await shelf_io.serve(
        handler,
        InternetAddress.anyIPv4,
        port,
        shared: true,
      );

      print('HTTP Server listening on port $port');

      // Log địa chỉ IP của student
      final interfaces = await NetworkInterface.list();
      final ip = interfaces
          .expand((interface) => interface.addresses)
          .firstWhere((addr) => addr.type == InternetAddressType.IPv4)
          .address;
      print('Student IP: $ip');
    } catch (e) {
      print('Error starting server: $e');
      rethrow;
    }
  }

  Future<shelf.Response> _handleRequest(shelf.Request request) async {
    try {
      // Xử lý GET request cho heartbeat
      if (request.method == 'GET' && request.url.path == 'heart-beat') {
        return shelf.Response.ok(
          json.encode({
            'status': 'success',
            'message': 'còn sống',
          }),
          headers: {'Content-Type': 'application/json'},
        );
      }

      return shelf.Response.notFound('Not found');
    } catch (e) {
      print('Error handling request: $e');
      return shelf.Response.internalServerError(
        body: json.encode({'error': e.toString()}),
        headers: {'Content-Type': 'application/json'},
      );
    }
  }

  Future<void> connectToTeacher(String ip) async {
    try {
      _teacherIP = ip;

      // Kiểm tra kết nối bằng HTTP request
      final response = await _checkTeacherConnection(ip);
      if (response.statusCode != 200) {
        throw Exception('Không thể kết nối tới teacher');
      }

      final data = json.decode(response.body);
      if (data['type'] != 'TEACHER_OK') {
        throw Exception('Không phải máy giáo viên');
      }

      print('✅ Đã kết nối thành công với teacher');

      // Gửi heartbeat định kỳ
      Timer.periodic(const Duration(seconds: 30), (timer) {
        if (isConnected) {
          _sendHeartbeat();
        } else {
          timer.cancel();
        }
      });
    } catch (e) {
      await disconnect();
      rethrow;
    }
  }

  Future<http.Response> _checkTeacherConnection(String ip) async {
    final url = 'http://$ip:$TEACHER_PORT/check';
    return await http.post(
      Uri.parse(url),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        'type': 'CHECK_TEACHER',
        'timestamp': DateTime.now().toIso8601String()
      }),
    );
  }

  Future<void> _sendHeartbeat() async {
    if (!isConnected) return;

    try {
      final url = 'http://$_teacherIP:$TEACHER_PORT/heartbeat';
      await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'type': 'HEARTBEAT',
          'timestamp': DateTime.now().toIso8601String()
        }),
      );
    } catch (e) {
      print('❌ Heartbeat failed: $e');
    }
  }

  Future<void> sendMessage(Map<String, dynamic> message) async {
    if (!isConnected) {
      throw Exception('Chưa kết nối tới teacher');
    }

    try {
      final url = 'http://$_teacherIP:$TEACHER_PORT/message';
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: json.encode(message),
      );

      if (response.statusCode != 200) {
        throw Exception('Gửi tin nhắn thất bại');
      }
    } catch (e) {
      print('❌ Lỗi khi gửi tin nhắn: $e');
      rethrow;
    }
  }

  Future<void> disconnect() async {
    _teacherIP = null;
    print('✅ Đã ngắt kết nối với teacher');
  }

  Future<void> dispose() async {
    await _server?.close();
    _server = null;
    await _messageController.close();
    print('✅ Đã đóng tất cả kết nối');
  }
}
