import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../../services/tcp_server_service.dart';
import '../../services/log_service.dart';
import '../../widgets/server_log_view.dart';
import '../../providers/tcp_server_provider.dart';
import 'dart:convert';
import 'dart:io';
import 'package:edudex_quiz_teacher_client/models/room_settings.dart';

class ExamRoomScreen extends StatefulWidget {
  final TcpServerService tcpService;
  final LogService logService;

  const ExamRoomScreen({
    super.key,
    required this.tcpService,
    required this.logService,
  });

  @override
  State<ExamRoomScreen> createState() => _ExamRoomScreenState();
}

class _ExamRoomScreenState extends State<ExamRoomScreen> {
  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Quản lý phòng thi'),
      ),
      content: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Consumer<TCPServerProvider>(
            builder: (context, provider, child) {
              // Gộp các client theo IP
              final Map<String, List<String>> clientsByIp = {};
              provider.connectedClients.keys.forEach((clientId) {
                final ip = clientId.split(':')[0];
                clientsByIp[ip] = clientsByIp[ip] ?? [];
                clientsByIp[ip]!.add(clientId);
              });

              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Server status
                  InfoBar(
                    title: Text(
                        'Trạng thái: ${provider.isRunning ? "Đang chạy" : "Đã dừng"}'),
                    severity: provider.isRunning
                        ? InfoBarSeverity.success
                        : InfoBarSeverity.warning,
                  ),
                  const SizedBox(height: 16),

                  // Server controls
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              FilledButton(
                                onPressed: () {
                                  if (provider.isRunning) {
                                    provider.stopServer();
                                  } else {
                                    provider.startServer();
                                  }
                                },
                                child: Row(
                                  children: [
                                    Icon(provider.isRunning
                                        ? FluentIcons.stop
                                        : FluentIcons.play),
                                    const SizedBox(width: 8),
                                    Text(provider.isRunning
                                        ? 'Dừng máy chủ'
                                        : 'Khởi động máy chủ'),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 16),
                              if (provider.isRunning) ...[
                                FutureBuilder<String?>(
                                  future: provider.getTeacherIp(),
                                  builder: (context, snapshot) {
                                    if (snapshot.hasData &&
                                        snapshot.data != null) {
                                      return Row(
                                        children: [
                                          Text('IP: ${snapshot.data}'),
                                          const SizedBox(width: 8),
                                          IconButton(
                                            icon: const Icon(FluentIcons.copy),
                                            onPressed: () {
                                              // Copy to clipboard
                                            },
                                          ),
                                        ],
                                      );
                                    }
                                    return const SizedBox.shrink();
                                  },
                                ),
                                const SizedBox(width: 16),
                                FilledButton(
                                  child: const Text('Xem log'),
                                  onPressed: () {
                                    showDialog(
                                      context: context,
                                      builder: (context) =>
                                          ServerLogView(logs: provider.logs),
                                    );
                                  },
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 16),
                          FilledButton(
                            child: const Text('Cài đặt phòng'),
                            onPressed: () {
                              showDialog(
                                context: context,
                                builder: (context) => _buildSettingsDialog(),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Connected clients grid
                  InfoLabel(
                    label:
                        'Máy đã kết nối (${clientsByIp.length})', // Số lượng IP unique
                    child: Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: GridView.builder(
                          shrinkWrap: true,
                          gridDelegate:
                              const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 10,
                            childAspectRatio: 1,
                            crossAxisSpacing: 10,
                            mainAxisSpacing: 10,
                          ),
                          itemCount: provider.settings.maxComputers,
                          itemBuilder: (context, index) {
                            final isConnected = index < clientsByIp.length;
                            final ip = isConnected
                                ? clientsByIp.keys.elementAt(index)
                                : 'MAY${(index + 1).toString().padLeft(2, '0')}';

                            return Card(
                              padding: const EdgeInsets.all(4),
                              child: GestureDetector(
                                onTap: isConnected
                                    ? () {
                                        showDialog(
                                          context: context,
                                          builder: (context) =>
                                              _buildClientModal(
                                            ip,
                                            clientsByIp[ip] ?? [],
                                          ),
                                        );
                                      }
                                    : null,
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(
                                      isConnected
                                          ? FluentIcons.device_bug
                                          : FluentIcons.device_off,
                                      size: 24,
                                      color: isConnected
                                          ? Colors.successPrimaryColor
                                          : Colors.grey[100],
                                    ),
                                    const SizedBox(height: 4),
                                    Tooltip(
                                      message: isConnected
                                          ? 'Số kết nối: ${clientsByIp[ip]?.length ?? 0}'
                                          : 'Chưa kết nối',
                                      child: Text(
                                        ip,
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: isConnected
                                              ? Colors.black
                                              : Colors.grey[100],
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildSettingsDialog() {
    final provider = context.read<TCPServerProvider>();
    final settings = provider.settings;

    final startIpController = TextEditingController(text: settings.startIp);
    final endIpController = TextEditingController(text: settings.endIp);
    final blockedIpsController =
        TextEditingController(text: settings.blockedIps.join(', '));
    final maxComputersController =
        TextEditingController(text: settings.maxComputers.toString());

    return ContentDialog(
      title: const Text('Cài đặt phòng thi'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          InfoLabel(
            label: 'Dải IP cho phép',
            child: Row(
              children: [
                Expanded(
                  child: TextBox(
                    placeholder: '192.168.0.1',
                    controller: startIpController,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextBox(
                    placeholder: '192.168.0.200',
                    controller: endIpController,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          InfoLabel(
            label: 'IP bị chặn',
            child: TextBox(
              placeholder: '192.168.0.100, 192.168.0.101',
              controller: blockedIpsController,
            ),
          ),
          const SizedBox(height: 8),
          InfoLabel(
            label: 'Số máy tối đa',
            child: NumberBox(
              value: settings.maxComputers.toDouble(),
              onChanged: (value) =>
                  maxComputersController.text = value?.toInt().toString() ?? '',
            ),
          ),
        ],
      ),
      actions: [
        Button(
          child: const Text('Hủy'),
          onPressed: () => Navigator.pop(context),
        ),
        FilledButton(
          child: const Text('Lưu'),
          onPressed: () {
            final newSettings = RoomSettings(
              startIp: startIpController.text,
              endIp: endIpController.text,
              blockedIps: blockedIpsController.text
                  .split(',')
                  .map((e) => e.trim())
                  .where((e) => e.isNotEmpty)
                  .toList(),
              maxComputers: int.tryParse(maxComputersController.text) ?? 50,
            );
            provider.updateSettings(newSettings);
            Navigator.pop(context);
          },
        ),
      ],
    );
  }

  Widget _buildClientModal(String ip, List<String> ports) {
    return ContentDialog(
      title: Text('Máy: $ip'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Số kết nối: ${ports.length}'),
          const SizedBox(height: 8),
          Text('Các cổng: ${ports.join(", ")}'),
          const SizedBox(height: 16),
          Row(
            children: [
              FilledButton(
                child: const Text('Kiểm tra kết nối'),
                onPressed: () async {
                  try {
                    final socket = await Socket.connect(ip, 8688);

                    // Gửi heartbeat
                    socket.write(json.encode({
                          'type': 'HEARTBEAT',
                          'timestamp': DateTime.now().toIso8601String(),
                        }) +
                        '\n');

                    // Đợi phản hồi
                    String buffer = '';
                    bool receivedResponse = false;

                    socket.listen(
                      (data) {
                        buffer += utf8.decode(data);
                        if (buffer.contains('\n')) {
                          final parts = buffer.split('\n');
                          final message = parts[0];

                          try {
                            final response = json.decode(message);
                            if (response['type'] == 'HEARTBEAT_RESPONSE') {
                              receivedResponse = true;
                              _showMessage(context, '✅ Kết nối thành công');
                            }
                          } catch (e) {
                            print('❌ Lỗi parse JSON: $e');
                          }
                        }
                      },
                      onDone: () {
                        socket.close();
                        if (!receivedResponse) {
                          _showMessage(context, '❌ Không nhận được phản hồi',
                              isError: true);
                        }
                      },
                    );

                    // Timeout sau 5 giây
                    Future.delayed(const Duration(seconds: 5), () {
                      if (!receivedResponse) {
                        socket.close();
                        _showMessage(context, '❌ Hết thời gian chờ phản hồi',
                            isError: true);
                      }
                    });
                  } catch (e) {
                    _showMessage(context, '❌ Không thể kết nối đến máy này: $e',
                        isError: true);
                  }
                },
              ),
              const SizedBox(width: 8),
              Button(
                child: const Text('Cấm IP này'),
                onPressed: () {
                  context.read<TCPServerProvider>().blockIp(ip);
                  Navigator.pop(context);
                },
              ),
            ],
          ),
        ],
      ),
      actions: [
        Button(
          child: const Text('Đóng'),
          onPressed: () => Navigator.pop(context),
        ),
      ],
    );
  }

  void _showMessage(BuildContext context, String message,
      {bool isError = false}) {
    showDialog(
      context: context,
      builder: (context) => ContentDialog(
        title: Text(message),
        actions: [
          Button(
            child: const Text('Đóng'),
            onPressed: () => Navigator.pop(context),
          ),
        ],
      ),
    );
  }
}
