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
  final Map<String, Socket> _connectedClients = {};

  late StreamController<Map<String, dynamic>> _messageController =
      StreamController<Map<String, dynamic>>.broadcast();
  Stream<Map<String, dynamic>> get messageStream => _messageController.stream;

  bool get isConnected =>
      _teacherSocket != null && _teacherSocket!.address.address == _teacherIP;

  Future<void> startServer({int port = STUDENT_PORT}) async {
    try {
      _server =
          await ServerSocket.bind(InternetAddress.anyIPv4, port, shared: true);
      print('TCP Server listening on port $port');

      final interfaces = await NetworkInterface.list();
      final ip = interfaces
          .expand((interface) => interface.addresses)
          .firstWhere((addr) => addr.type == InternetAddressType.IPv4)
          .address;
      print('Student IP: $ip');

      _server!.listen((Socket client) {
        final clientIp = client.remoteAddress.address;
        final clientId = '$clientIp:${client.remotePort}';

        print('📥 Nhận kết nối từ: $clientId');

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
                    print('📥 Nhận tin nhắn: $jsonData');

                    switch (jsonData['type']) {
                      case 'HEARTBEAT':
                        print('💓 Nhận heartbeat từ teacher');
                        client.write(json.encode({
                              'type': 'HEARTBEAT_RESPONSE',
                              'timestamp': DateTime.now().toIso8601String(),
                            }) +
                            '\n');
                        print('💓 Đã gửi phản hồi heartbeat');
                        break;

                      default:
                        _messageController.add(jsonData);
                        break;
                    }
                  } catch (e) {
                    print('❌ Lỗi parse JSON: $e');
                  }
                }
              }
            } catch (e) {
              print('❌ Lỗi xử lý dữ liệu: $e');
            }
          },
          onError: (error) {
            print('❌ Lỗi kết nối: $error');
          },
          onDone: () {
            print('Client ${client.remoteAddress.address} ngắt kết nối');
          },
          cancelOnError: false,
        );
      });
    } catch (e) {
      print('Error starting server: $e');
      rethrow;
    }
  }

  Future<void> connectToTeacher(String ip) async {
    try {
      _teacherIP = ip;
      _teacherSocket = await Socket.connect(ip, TEACHER_PORT,
          timeout: const Duration(seconds: 5));

      // Thiết lập listener trước
      _setupSocketListener();

      // Gửi CHECK_TEACHER
      _sendJson({
        'type': 'CHECK_TEACHER',
        'timestamp': DateTime.now().toIso8601String()
      });

      // Đợi TEACHER_OK và giữ kết nối
      await _messageController.stream
          .firstWhere((msg) => msg['type'] == 'TEACHER_OK')
          .timeout(const Duration(seconds: 5));

      print('✅ Đã kết nối thành công với teacher');

      // Gửi heartbeat định kỳ
      Timer.periodic(const Duration(seconds: 30), (timer) {
        if (isConnected) {
          _sendJson({
            'type': 'HEARTBEAT',
            'timestamp': DateTime.now().toIso8601String()
          });
        } else {
          timer.cancel();
        }
      });
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
      },
      onDone: () {
        print('❌ Mất kết nối với teacher');
      },
      cancelOnError: false,
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
