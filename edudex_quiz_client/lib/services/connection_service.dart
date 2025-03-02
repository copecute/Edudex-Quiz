import 'dart:io';
import 'dart:async';
import 'dart:convert';

class ConnectionService {
  static final ConnectionService _instance = ConnectionService._internal();
  factory ConnectionService() => _instance;
  ConnectionService._internal();

  static const int STUDENT_PORT = 8688;
  static const int TEACHER_PORT = 8689;

  ServerSocket? _server;
  Socket? _teacherSocket;
  String? _teacherIP;

  late StreamController<Map<String, dynamic>> _messageController =
      StreamController<Map<String, dynamic>>.broadcast();
  Stream<Map<String, dynamic>> get messageStream => _messageController.stream;

  bool get isConnected =>
      _teacherSocket != null && _teacherSocket!.address.address == _teacherIP;

  Future<void> startServer() async {
    if (_server != null) return;

    try {
      _server = await ServerSocket.bind('0.0.0.0', STUDENT_PORT);
      print('✅ Student server đang lắng nghe trên port $STUDENT_PORT');

      _server!.listen(
        (socket) {
          print(
              '📥 Nhận kết nối từ: ${socket.remoteAddress.address}:${socket.remotePort}');

          if (socket.remoteAddress.address != _teacherIP) {
            print('❌ Từ chối kết nối không phải từ teacher');
            socket.close();
            return;
          }

          String buffer = '';
          socket.listen(
            (data) {
              buffer += utf8.decode(data);

              while (buffer.contains('\n')) {
                final parts = buffer.split('\n');
                final message = parts[0];
                buffer = parts.sublist(1).join('\n');

                if (message.isNotEmpty) {
                  try {
                    final jsonData = json.decode(message);
                    print('📥 Nhận tin nhắn từ teacher: $jsonData');
                    _messageController.add(jsonData);
                  } catch (e) {
                    print('❌ Lỗi parse JSON: $e');
                  }
                }
              }
            },
            onError: (error) => print('❌ Lỗi khi nhận tin nhắn: $error'),
            onDone: () {
              print('❌ Teacher đã ngắt kết nối');
              socket.close();
            },
          );
        },
        onError: (error) => print('❌ Lỗi server: $error'),
      );
    } catch (e) {
      print('❌ Không thể khởi tạo server: $e');
      rethrow;
    }
  }

  Future<void> connectToTeacher(String ip) async {
    String errorMessage = '';
    try {
      _teacherIP = ip;

      // Kết nối tới teacher
      try {
        _teacherSocket = await Socket.connect(ip, TEACHER_PORT,
            timeout: const Duration(seconds: 5));
      } on SocketException catch (e) {
        // Xử lý chi tiết từng loại lỗi socket
        if (e.osError?.errorCode == 10061) {
          // Connection refused
          errorMessage = '''
Không thể kết nối tới teacher!!

Nguyên nhân: Port 8689 bị từ chối kết nối
Hướng dẫn:
1. Kiểm tra teacher đã khởi động chưa
2. Kiểm tra tường lửa có chặn port 8689
3. Đảm bảo IP teacher ($ip) chính xác

Chi tiết: ${e.message}''';
        } else if (e.osError?.errorCode == 10060) {
          // Connection timed out
          errorMessage = '''
Không thể kết nối tới teacher!!!

Nguyên nhân: Kết nối bị timeout
Hướng dẫn:
1. Kiểm tra IP teacher ($ip) có đúng không
2. Đảm bảo teacher và student trong cùng mạng LAN
3. Thử tắt tường lửa và antivirus

Chi tiết: ${e.message}''';
        } else {
          errorMessage = '''
Không thể kết nối tới teacher!!!!

Nguyên nhân: Lỗi kết nối mạng
Hướng dẫn:
1. Kiểm tra kết nối mạng
2. Kiểm tra IP teacher ($ip)
3. Đảm bảo teacher đang chạy

Mã lỗi: ${e.osError?.errorCode}
Chi tiết: ${e.message}''';
        }
        throw Exception(errorMessage);
      }

      // Thiết lập và gửi message
      try {
        await _messageController.close();
        _messageController = StreamController<Map<String, dynamic>>.broadcast();
        _setupSocketListener();
        _sendJson({
          'type': 'CHECK_TEACHER',
          'timestamp': DateTime.now().toIso8601String()
        });
      } catch (e) {
        errorMessage = '''
Lỗi khi thiết lập kết nối!

Hướng dẫn:
1. Thử khởi động lại ứng dụng
2. Kiểm tra quyền truy cập mạng

Chi tiết lỗi: $e''';
        throw Exception(errorMessage);
      }

      // Đợi response
      try {
        final response = await _messageController.stream.first
            .timeout(const Duration(seconds: 5));

        print('📥 Nhận được response từ teacher: $response');

        if (response['type'] != 'TEACHER_OK') {
          throw Exception('''
Phản hồi không hợp lệ từ teacher!

Hướng dẫn:
1. Kiểm tra phiên bản phần mềm teacher và student
2. Thử khởi động lại cả teacher và student

Response type nhận được: ${response['type']}
Response data: $response''');
        }
      } catch (e) {
        if (e is TimeoutException) {
          errorMessage = '''
Không nhận được phản hồi từ teacher!

Hướng dẫn:
1. Kiểm tra teacher có đang chạy không
2. Kiểm tra IP teacher đã nhập đúng chưa
3. Kiểm tra kết nối mạng giữa teacher và student
4. Đảm bảo port 8689 không bị chặn''';
        } else {
          errorMessage = '''
Lỗi khi chờ phản hồi từ teacher!

Hướng dẫn:
1. Thử kết nối lại
2. Kiểm tra kết nối mạng

Chi tiết lỗi: $e''';
        }
        throw Exception(errorMessage);
      }
    } catch (e) {
      await disconnect();
      rethrow;
    }
  }

  void _setupSocketListener() {
    String buffer = '';
    _teacherSocket!.listen(
      (data) {
        buffer += utf8.decode(data);
        while (buffer.contains('\n')) {
          final parts = buffer.split('\n');
          final message = parts[0];
          buffer = parts.sublist(1).join('\n');

          if (message.isNotEmpty) {
            try {
              final jsonData = json.decode(message);
              print('📥 Nhận tin nhắn từ teacher: $jsonData');
              _messageController.add(jsonData);
            } catch (e) {
              print('❌ Lỗi parse JSON: $e');
            }
          }
        }
      },
      onError: (error) {
        print('❌ Lỗi kết nối: $error');
        disconnect();
      },
      onDone: () {
        print('❌ Mất kết nối với teacher');
        disconnect();
      },
    );
  }

  void _sendJson(Map<String, dynamic> data) {
    final jsonStr = json.encode(data);
    _teacherSocket!.write('$jsonStr\n');
    print('📤 Đã gửi tin nhắn: $jsonStr');
  }

  Future<void> sendMessage(Map<String, dynamic> message) async {
    if (!isConnected) {
      throw Exception('Chưa kết nối tới teacher');
    }
    try {
      _sendJson(message);
    } catch (e) {
      print('❌ Lỗi khi gửi tin nhắn: $e');
      rethrow;
    }
  }

  Future<void> disconnect() async {
    try {
      await _teacherSocket?.close();
      _teacherSocket = null;
      _teacherIP = null;
      print('✅ Đã ngắt kết nối với teacher');
    } catch (e) {
      print('⚠️ Lỗi khi ngắt kết nối: $e');
    }
  }

  Future<void> dispose() async {
    await disconnect();
    await _server?.close();
    _server = null;
    await _messageController.close();
    print('✅ Đã đóng tất cả kết nối');
  }
}
