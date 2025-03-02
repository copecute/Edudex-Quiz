class LogService {
  final List<LogEntry> _logs = [];
  Function(List<LogEntry>)? onLogsChanged;

  void log(String message, {LogLevel level = LogLevel.info}) {
    final entry = LogEntry(
      message: message,
      level: level,
      timestamp: DateTime.now(),
    );
    _logs.add(entry);
    onLogsChanged?.call(_logs);
  }

  List<LogEntry> get logs => List.unmodifiable(_logs);

  void clear() {
    _logs.clear();
    onLogsChanged?.call(_logs);
  }
}

enum LogLevel {
  info,
  warning,
  error,
}

class LogEntry {
  final String message;
  final LogLevel level;
  final DateTime timestamp;

  LogEntry({
    required this.message,
    required this.level,
    required this.timestamp,
  });
}
