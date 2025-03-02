import 'dart:io';
import 'package:flutter/foundation.dart';
import '../services/tcp_server_service.dart';
import '../services/database_service.dart';
import '../services/log_service.dart';
import '../models/room_settings.dart';

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
  Map<String, Socket> get connectedClients => _tcpService.connectedClients;
  int get connectedClientCount => connectedClients.length;

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

  void blockIp(String ip) {
    final settings = RoomSettings(
      startIp: '192.168.0.10',
      endIp: '192.168.0.200',
      blockedIps: [..._tcpService.settings.blockedIps, ip],
      maxComputers: _tcpService.settings.maxComputers,
    );
    _tcpService.updateSettings(settings);
    notifyListeners();
  }

  RoomSettings get settings => _tcpService.settings;

  void updateSettings(RoomSettings newSettings) {
    _tcpService.updateSettings(newSettings);
    notifyListeners();
  }
}
