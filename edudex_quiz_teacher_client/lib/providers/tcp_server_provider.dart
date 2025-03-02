import 'package:flutter/foundation.dart';
import '../services/tcp_server_service.dart';
import '../services/database_service.dart';
import '../services/log_service.dart';

class TCPServerProvider extends ChangeNotifier {
  final TcpServerService _tcpService;
  final DatabaseService _dbService;
  final LogService _logService;
  bool _isRunning = false;
  String? _error;
  int _connectedClients = 0;

  TCPServerProvider({
    required TcpServerService tcpService,
    required DatabaseService dbService,
    required LogService logService,
  })  : _tcpService = tcpService,
        _dbService = dbService,
        _logService = logService {
    _tcpService.onClientCountChanged = updateConnectedClients;
    _logService.onLogsChanged = _handleLogsChanged;
  }

  List<LogEntry> get logs => _logService.logs;

  void _handleLogsChanged(List<LogEntry> logs) {
    notifyListeners();
  }

  bool get isRunning => _isRunning;
  String? get error => _error;
  int get connectedClients => _connectedClients;

  Future<void> startServer({int port = 8689}) async {
    try {
      await _tcpService.startServer(port: port);
      _isRunning = true;
      _error = null;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isRunning = false;
      notifyListeners();
      print('Error starting TCP server: $e');
    }
  }

  void stopServer() {
    _tcpService.stopServer();
    _isRunning = false;
    _connectedClients = 0;
    notifyListeners();
  }

  void updateConnectedClients(int count) {
    _connectedClients = count;
    notifyListeners();
  }

  Future<String?> getTeacherIp() async {
    return await _tcpService.localIp;
  }
}
