import 'dart:io';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:async';

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

      // Xử lý requests
      _server!.listen((HttpRequest request) async {
        request.response.headers.contentType = ContentType.json;

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
}
