import 'package:fluent_ui/fluent_ui.dart';
import '../services/log_service.dart';

class ServerLogView extends StatelessWidget {
  final List<LogEntry> logs;

  const ServerLogView({super.key, required this.logs});

  @override
  Widget build(BuildContext context) {
    return ContentDialog(
      title: const Text('Server Log'),
      content: SizedBox(
        width: 600,
        height: 400,
        child: SingleChildScrollView(
          child: Column(
            children: [
              SelectableText(
                logs
                    .map((log) =>
                        '[${_formatTime(log.timestamp)}] ${log.message}')
                    .join('\n'),
                style: TextStyle(
                  color: Colors.black,
                ),
              ),
            ],
          ),
        ),
      ),
      actions: [
        Button(
          child: const Text('Đóng'),
          onPressed: () => Navigator.pop(context),
        ),
      ],
    );
  }

  String _formatTime(DateTime time) {
    return '${time.hour.toString().padLeft(2, '0')}:'
        '${time.minute.toString().padLeft(2, '0')}:'
        '${time.second.toString().padLeft(2, '0')}';
  }

  Color _getColorForLevel(LogLevel level) {
    switch (level) {
      case LogLevel.error:
        return Colors.errorPrimaryColor;
      case LogLevel.warning:
        return Colors.warningPrimaryColor;
      case LogLevel.info:
        return Colors.black;
    }
  }
}
