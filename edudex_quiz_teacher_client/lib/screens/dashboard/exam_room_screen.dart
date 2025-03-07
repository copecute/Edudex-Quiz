import 'package:fluent_ui/fluent_ui.dart';
import '../../services/http_server_service.dart';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../../models/exam_schedule.dart';
import 'package:flutter/services.dart';
import 'dart:async';
import 'package:flutter/rendering.dart';
import 'package:http/http.dart' as http;

class ExamRoomScreen extends StatefulWidget {
  final HttpServerService httpService;

  const ExamRoomScreen({
    super.key,
    required this.httpService,
  });

  @override
  State<ExamRoomScreen> createState() => _ExamRoomScreenState();
}

class _ExamRoomScreenState extends State<ExamRoomScreen>
    with AutomaticKeepAliveClientMixin {
  int _roomCapacity = 0;
  bool _isRunning = false;
  String? _teacherIp;
  final _startIpController = TextEditingController();
  final _endIpController = TextEditingController();
  final _blockedIpsController = TextEditingController();

  Timer? _refreshTimer;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _loadRoomCapacity();
    _checkServerStatus();
    _loadServerSettings();

    // Thêm timer để cập nhật trạng thái định kỳ
    _refreshTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {});
      }
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _startIpController.dispose();
    _endIpController.dispose();
    _blockedIpsController.dispose();
    super.dispose();
  }

  void _checkServerStatus() {
    setState(() {
      _isRunning = widget.httpService.isRunning;
      _teacherIp = widget.httpService.teacherIp;
    });
  }

  Future<void> _loadRoomCapacity() async {
    final prefs = await SharedPreferences.getInstance();

    // Load thông tin lịch thi
    final scheduleData = prefs.getString('exam_schedule');
    if (scheduleData != null) {
      final selectedRoomId = prefs.getInt('selected_room_id');
      if (selectedRoomId != null) {
        try {
          final scheduleJson = json.decode(scheduleData) as List;
          final periods = scheduleJson
              .map((period) => ExamPeriod.fromJson(period))
              .toList();

          // Tìm phòng được chọn
          for (var period in periods) {
            for (var shift in period.shifts) {
              for (var room in shift.rooms) {
                if (room.id == selectedRoomId) {
                  final capacity = room.capacity ?? 0;
                  setState(() {
                    _roomCapacity = capacity;
                  });
                  // Lưu room_capacity vào SharedPreferences
                  await prefs.setInt('room_capacity', capacity);
                  return;
                }
              }
            }
          }
        } catch (e) {
          print('Lỗi load room capacity: $e');
        }
      }
    }
  }

  Future<void> _loadServerSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _startIpController.text =
          prefs.getString('server_start_ip') ?? '192.168.1.1';
      _endIpController.text =
          prefs.getString('server_end_ip') ?? '192.168.1.255';
      _blockedIpsController.text = prefs.getString('server_blocked_ips') ?? '';
    });
  }

  Future<void> _saveServerSettings() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('server_start_ip', _startIpController.text);
    await prefs.setString('server_end_ip', _endIpController.text);
    await prefs.setString('server_blocked_ips', _blockedIpsController.text);
  }

  void _showSettingsDialog() {
    showDialog(
      context: context,
      builder: (context) => ContentDialog(
        title: Row(
          children: const [
            Icon(FluentIcons.settings),
            SizedBox(width: 8),
            Text('Cài đặt máy chủ'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            InfoLabel(
              label: 'Dải IP cho phép',
              child: Row(
                children: [
                  Expanded(
                    child: TextBox(
                      placeholder: '192.168.1.1',
                      controller: _startIpController,
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Text('đến'),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextBox(
                      placeholder: '192.168.1.255',
                      controller: _endIpController,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            InfoLabel(
              label: 'IP bị chặn (phân cách bằng dấu phẩy)',
              child: TextBox(
                placeholder: 'VD: 192.168.1.5, 192.168.1.10',
                controller: _blockedIpsController,
                maxLines: 3,
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
            onPressed: () async {
              await _saveServerSettings();
              if (!mounted) return;
              Navigator.pop(context);
              displayInfoBar(
                context,
                builder: (context, close) {
                  return InfoBar(
                    title: const Text('Đã lưu cài đặt máy chủ'),
                    severity: InfoBarSeverity.success,
                    onClose: close,
                  );
                },
              );
            },
          ),
        ],
      ),
    );
  }

  void _showComputerInfoDialog(
      int computerNumber, String clientIp, DateTime connectionTime) {
    showDialog(
      context: context,
      builder: (context) => ContentDialog(
        constraints: const BoxConstraints(maxWidth: 400),
        title: Row(
          children: [
            Icon(FluentIcons.system,
                color: FluentTheme.of(context).accentColor),
            const SizedBox(width: 8),
            Text('Máy $computerNumber'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Thông tin cơ bản
            _buildInfoGroup('Thông tin máy', [
              _buildInfoRow('Tên máy:', 'PC-$computerNumber'),
              _buildInfoRow('Địa chỉ IP:', clientIp),
              _buildInfoRow(
                'Thời gian kết nối:',
                '${connectionTime.hour.toString().padLeft(2, '0')}:${connectionTime.minute.toString().padLeft(2, '0')}:${connectionTime.second.toString().padLeft(2, '0')}',
              ),
            ]),

            const SizedBox(height: 16),

            // Thông tin thí sinh
            _buildInfoGroup('Thông tin thí sinh', [
              _buildInfoRow('Họ tên:', 'Chưa đăng nhập'),
              _buildInfoRow('Số báo danh:', 'Chưa đăng nhập'),
              _buildInfoRow('Mã sinh viên:', 'Chưa đăng nhập'),
            ]),
          ],
        ),
        actions: [
          Button(
            child: const Text('Kiểm tra kết nối'),
            onPressed: () async {
              try {
                final response = await http.get(
                  Uri.parse('http://$clientIp:8688/heart-beat'),
                );

                if (!mounted) return;

                if (response.statusCode == 200) {
                  final data = json.decode(response.body);
                  if (data['status'] == 'success') {
                    displayInfoBar(
                      context,
                      builder: (context, close) {
                        return InfoBar(
                          title: const Text('Máy đang hoạt động'),
                          content: Text(data['messages']),
                          severity: InfoBarSeverity.success,
                          onClose: close,
                        );
                      },
                    );
                  }
                } else {
                  throw Exception('Không thể kết nối');
                }
              } catch (e) {
                if (!mounted) return;
                displayInfoBar(
                  context,
                  builder: (context, close) {
                    return InfoBar(
                      title: const Text('Lỗi kết nối'),
                      content: const Text('Không thể kết nối đến máy'),
                      severity: InfoBarSeverity.error,
                      onClose: close,
                    );
                  },
                );
              }
            },
          ),
          Button(
            child: const Text('Cấm IP này'),
            onPressed: () async {
              final prefs = await SharedPreferences.getInstance();
              final blockedIps = prefs.getString('server_blocked_ips') ?? '';
              final newBlockedIps =
                  blockedIps.isEmpty ? clientIp : '$blockedIps, $clientIp';

              // Lưu vào SharedPreferences
              await prefs.setString('server_blocked_ips', newBlockedIps);

              // Cập nhật controller
              setState(() {
                _blockedIpsController.text = newBlockedIps;
              });

              // Xóa máy khỏi danh sách kết nối
              widget.httpService.disconnectComputer(computerNumber);

              if (!mounted) return;
              Navigator.pop(context);
              displayInfoBar(
                context,
                builder: (context, close) {
                  return InfoBar(
                    title: Text('Đã chặn IP: $clientIp'),
                    severity: InfoBarSeverity.warning,
                    onClose: close,
                  );
                },
              );
            },
          ),
          FilledButton(
            child: const Text('Đóng'),
            onPressed: () => Navigator.pop(context),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoGroup(String title, List<Widget> children) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        ...children,
      ],
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(color: Colors.grey),
            ),
          ),
          Expanded(
            child: Text(value),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);

    // Tính toán số cột dựa vào kích thước màn hình
    final width = MediaQuery.of(context).size.width;
    final crossAxisCount = switch (width) {
      > 1600 => 12,
      > 1200 => 10,
      > 800 => 8,
      > 600 => 6,
      _ => 4,
    };

    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Quản lý phòng thi'),
      ),
      content: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Server Controls Section
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(FluentIcons.server),
                          const SizedBox(width: 12),
                          Text(
                            'Máy chủ',
                            style: FluentTheme.of(context).typography.subtitle,
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      InfoBar(
                        title: Text(
                          'Trạng thái: ${_isRunning ? "Đang chạy" : "Đã dừng"}',
                        ),
                        severity: _isRunning
                            ? InfoBarSeverity.success
                            : InfoBarSeverity.warning,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          FilledButton(
                            onPressed: () async {
                              if (_isRunning) {
                                await widget.httpService.stopServer();
                              } else {
                                await widget.httpService.startServer();
                              }
                              _checkServerStatus();
                            },
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(_isRunning
                                    ? FluentIcons.stop
                                    : FluentIcons.play),
                                const SizedBox(width: 8),
                                Text(_isRunning
                                    ? 'Dừng máy chủ'
                                    : 'Khởi động máy chủ'),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          IconButton(
                            icon: const Icon(FluentIcons.settings),
                            onPressed: _showSettingsDialog,
                          ),
                          if (_isRunning && _teacherIp != null) ...[
                            const SizedBox(width: 16),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: FluentTheme.of(context).cardColor,
                                borderRadius: BorderRadius.circular(4),
                                border: Border.all(
                                  color:
                                      FluentTheme.of(context).brightness.isDark
                                          ? Colors.white.withOpacity(0.1)
                                          : Colors.black.withOpacity(0.1),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(FluentIcons.globe, size: 16),
                                  const SizedBox(width: 8),
                                  Text('$_teacherIp:8689'),
                                  const SizedBox(width: 8),
                                  IconButton(
                                    icon:
                                        const Icon(FluentIcons.copy, size: 16),
                                    onPressed: () async {
                                      await Clipboard.setData(
                                        ClipboardData(text: '$_teacherIp:8689'),
                                      );
                                      if (!mounted) return;
                                      displayInfoBar(
                                        context,
                                        builder: (context, close) {
                                          return InfoBar(
                                            title: Text(
                                              'Đã sao chép IP: $_teacherIp:8689',
                                            ),
                                            severity: InfoBarSeverity.success,
                                            onClose: close,
                                          );
                                        },
                                      );
                                    },
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Computer Grid Section
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              const Icon(FluentIcons.devices4),
                              const SizedBox(width: 12),
                              Text(
                                'Danh sách máy thi',
                                style:
                                    FluentTheme.of(context).typography.subtitle,
                              ),
                            ],
                          ),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.green.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  'Đã kết nối: ${widget.httpService.connectedComputers.length}',
                                  style: const TextStyle(
                                    color: Colors.successPrimaryColor,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: FluentTheme.of(context)
                                      .accentColor
                                      .withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  'Tổng số: $_roomCapacity máy',
                                  style: TextStyle(
                                    color: FluentTheme.of(context).accentColor,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          childAspectRatio: 1,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                        ),
                        itemCount: _roomCapacity,
                        itemBuilder: (context, index) {
                          final computerNumber = index + 1;
                          final isConnected = widget
                              .httpService.connectedComputers
                              .containsKey(computerNumber);
                          final clientIp = widget
                              .httpService.connectedComputers[computerNumber];
                          final isAlive = widget.httpService
                                  .connectionStatus[computerNumber] ??
                              true;

                          return HoverButton(
                            builder: (context, states) => Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: isConnected
                                    ? (isAlive
                                            ? Colors.green.withOpacity(0.1)
                                            : Colors.errorPrimaryColor)
                                        .withOpacity(0.1)
                                    : null,
                              ),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    FluentIcons.system,
                                    size: 32,
                                    color: isConnected
                                        ? (isAlive
                                            ? Colors.green
                                            : Colors.errorPrimaryColor)
                                        : Colors.grey,
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    'Máy $computerNumber',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w500,
                                      color: isConnected
                                          ? (isAlive
                                              ? Colors.green
                                              : Colors.errorPrimaryColor)
                                          : Colors.grey,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    isConnected
                                        ? (isAlive
                                            ? 'Đã kết nối'
                                            : 'Mất kết nối')
                                        : 'Chưa kết nối',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isConnected
                                          ? (isAlive
                                              ? Colors.green
                                              : Colors.errorPrimaryColor)
                                          : Colors.grey,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            onPressed: isConnected
                                ? () {
                                    final connectionTime = widget.httpService
                                        .connectionTimes[computerNumber];
                                    if (connectionTime != null) {
                                      _showComputerInfoDialog(computerNumber,
                                          clientIp!, connectionTime);
                                    }
                                  }
                                : null,
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
