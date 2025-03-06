import 'package:fluent_ui/fluent_ui.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../../models/exam_schedule.dart';
import '../../widgets/exam_period_selector.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final _serverUrlController = TextEditingController();
  String _username = '';
  String _email = '';
  String? _serverUrl;
  int _role = 0;
  bool _isConnected = true;
  bool _isCheckingConnection = false;
  String? _lastRequestTime;
  String? _lastResponse;
  List<ExamPeriod> _examPeriods = [];
  bool _isLoading = true;
  String? _error;
  ExamPeriod? _selectedPeriod;
  ExamShift? _selectedShift;
  ExamRoom? _selectedRoom;

  // Thêm các key constants
  static const String SELECTED_SHIFT_ID = 'selected_shift_id';
  static const String SELECTED_ROOM_ID = 'selected_room_id';
  static const String SELECTED_SUBJECT_ID = 'selected_subject_id';
  static const String SELECTED_EXAM_ID = 'selected_exam_id';
  static const String SELECTED_PERIOD_ID = 'selected_period_id';

  bool _isExamSelected = false; // Biến để theo dõi trạng thái đã chọn

  @override
  void initState() {
    super.initState();
    _loadUserInfo();
    _loadExamSchedule();
    _checkSelectedExam(); // Kiểm tra xem đã chọn ca thi hay chưa
  }

  @override
  void dispose() {
    _serverUrlController.dispose();
    super.dispose();
  }

  Future<void> _loadUserInfo() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _username = prefs.getString('username') ?? 'Không xác định';
      _email = prefs.getString('email') ?? 'Không xác định';
      _serverUrl = prefs.getString('server_url');
      _serverUrlController.text = _formatServerUrl(_serverUrl);
      _role = prefs.getInt('user_role') ?? 0;
    });
    print(
        '👤 Loaded user info - Username: $_username, Email: $_email, Role: $_role');
  }

  String _formatServerUrl(String? url) {
    if (url == null) return 'Chưa cấu hình';
    return url.replaceFirst(RegExp(r'https?://'), '');
  }

  Future<void> _checkServerConnection() async {
    if (_serverUrl == null) return;

    setState(() {
      _isCheckingConnection = true;
    });

    final startTime = DateTime.now();
    try {
      final response = await http.post(
        Uri.parse('$_serverUrl/api/wfaE0FbQWldGoDlGFyFgKWY0MiUizH2'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      final endTime = DateTime.now();
      final duration = endTime.difference(startTime);

      final data = json.decode(response.body);
      setState(() {
        _isConnected = data['messages'] == 'copecute is beautiful';
        _isCheckingConnection = false;
        _lastRequestTime = '${duration.inMilliseconds}ms';
        _lastResponse = data['messages'];
      });

      print('✅ Phản hồi từ máy chủ: ${data['messages']}');
      print('⏱️ Thời gian phản hồi: ${duration.inMilliseconds}ms');
    } catch (e) {
      print('🔥 Lỗi khi kiểm tra kết nối: $e');
      setState(() {
        _isConnected = false;
        _isCheckingConnection = false;
        _lastRequestTime = null;
        _lastResponse = null;
      });
    }
  }

  Future<void> _checkSelectedExam() async {
    final prefs = await SharedPreferences.getInstance();
    final selectedPeriodId = prefs.getInt(SELECTED_PERIOD_ID);
    final selectedShiftId = prefs.getInt(SELECTED_SHIFT_ID);
    final selectedRoomId = prefs.getInt(SELECTED_ROOM_ID);

    if (selectedPeriodId != null &&
        selectedShiftId != null &&
        selectedRoomId != null) {
      // Nếu đã có thông tin đã chọn, đánh dấu là đã chọn
      setState(() {
        _isExamSelected = true;
        // Tìm kiếm thông tin đã chọn từ _examPeriods
        _selectedPeriod =
            _examPeriods.firstWhere((period) => period.id == selectedPeriodId);
        _selectedShift = _selectedPeriod!.shifts
            .firstWhere((shift) => shift.id == selectedShiftId);
        _selectedRoom = _selectedShift!.rooms
            .firstWhere((room) => room.id == selectedRoomId);
      });
    }
  }

  Future<void> _loadExamSchedule() async {
    if (_examPeriods.isNotEmpty)
      return; // Nếu đã có lịch thi, không gọi lại API

    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('user_token');
      final serverUrl = prefs.getString('server_url');

      if (token == null || serverUrl == null) {
        throw Exception('Không tìm thấy thông tin đăng nhập');
      }

      final response = await http.get(
        Uri.parse('$serverUrl/api/exam-schedule'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'copecute $token',
        },
      );

      final data = json.decode(response.body);

      if (data['success'] == true) {
        if (data['data'].isEmpty) {
          setState(() {
            _examPeriods = [];
            _error = data['message'];
          });
        } else {
          setState(() {
            _examPeriods = (data['data'] as List).map((periodData) {
              final examPeriod = periodData['exam_period'];
              return ExamPeriod(
                id: examPeriod['id'],
                name: examPeriod['name'],
                startTime: DateTime.parse(examPeriod['start_time']),
                endTime: DateTime.parse(examPeriod['end_time']),
                shifts: (periodData['shifts'] as List)
                    .map((shift) => ExamShift.fromJson(shift))
                    .toList(),
              );
            }).toList();
          });

          // Chỉ hiển thị dialog nếu chưa chọn ca thi
          if (mounted && _examPeriods.isNotEmpty && !_isExamSelected) {
            showDialog(
              context: context,
              builder: (context) => ExamPeriodSelector(
                examPeriods: _examPeriods,
                onSelected: (period, shift, room) async {
                  setState(() {
                    _selectedPeriod = period;
                    _selectedShift = shift;
                    _selectedRoom = room;
                    _isExamSelected = true; // Đánh dấu đã chọn
                  });

                  // Lưu các ID vào SharedPreferences
                  final prefs = await SharedPreferences.getInstance();
                  await prefs.setInt(SELECTED_PERIOD_ID, period.id ?? 0);
                  await prefs.setInt(SELECTED_SHIFT_ID, shift.id ?? 0);
                  await prefs.setInt(SELECTED_ROOM_ID, room.id ?? 0);
                  await prefs.setInt(SELECTED_SUBJECT_ID, room.subject.id ?? 0);
                  await prefs.setInt(
                      SELECTED_EXAM_ID, room.subject.exam?.id ?? 0);

                  print('📅 Đã chọn kỳ thi: ${period.name} (ID: ${period.id})');
                  print('⏰ Đã chọn ca thi: ${shift.name} (ID: ${shift.id})');
                  print('🏫 Đã chọn phòng: ${room.name} (ID: ${room.id})');
                  print(
                      '📚 Đã chọn môn: ${room.subject.name} (ID: ${room.subject.id})');
                  if (room.subject.exam != null) {
                    print(
                        '📝 Đã chọn đề thi: ${room.subject.exam!.name} (ID: ${room.subject.exam!.id})');
                  }
                },
              ),
            );
          }
        }
      } else {
        throw Exception(data['message']);
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
      print('🔥 Lỗi load lịch thi: $e'); // Thêm log để debug
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  String _getRoleName(int role) {
    switch (role) {
      case 0:
        return 'Cán bộ coi thi';
      case 1:
        return 'Giáo viên';
      case 2:
        return 'Quản trị hệ thống';
      default:
        return 'Không xác định';
    }
  }

  Widget _buildSelectedExamInfo() {
    if (_selectedPeriod == null || _selectedShift == null) {
      return const SizedBox.shrink();
    }

    return Card(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Thông tin ca thi đã chọn',
            style: FluentTheme.of(context).typography.subtitle,
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInfoRow(
                      icon: FluentIcons.calendar,
                      label: 'Kỳ thi:',
                      value: _selectedPeriod!.name,
                    ),
                    const SizedBox(height: 8),
                    _buildInfoRow(
                      icon: FluentIcons.clock,
                      label: 'Ca thi:',
                      value: '${_selectedShift!.name}\n'
                          '${_formatDateTime(_selectedShift!.startTime)} - '
                          '${_formatDateTime(_selectedShift!.endTime)}',
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 32),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInfoRow(
                      icon: FluentIcons.room,
                      label: 'Phòng thi:',
                      value:
                          '${_selectedRoom?.name} (${_selectedRoom?.facility})',
                    ),
                    const SizedBox(height: 8),
                    _buildInfoRow(
                      icon: FluentIcons.custom_entity,
                      label: 'Môn thi:',
                      value: _selectedRoom?.subject.name ?? '',
                    ),
                    if (_selectedRoom?.subject.exam != null) ...[
                      const SizedBox(height: 8),
                      _buildInfoRow(
                        icon: FluentIcons.diet_plan_notebook,
                        label: 'Đề thi:',
                        value: '${_selectedRoom?.subject.exam?.name}\n'
                            'Thời gian: ${_selectedRoom?.subject.exam?.duration} phút\n'
                            'Số câu hỏi: ${_selectedRoom?.subject.exam?.totalQuestions}',
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              Text(value),
            ],
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      content: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome section
            Card(
              padding: const EdgeInsets.all(24.0),
              child: Row(
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Chào mừng, $_username!',
                        style: FluentTheme.of(context).typography.titleLarge,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _email,
                        style: FluentTheme.of(context).typography.body,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Vai trò: ${_getRoleName(_role)}',
                        style:
                            FluentTheme.of(context).typography.body?.copyWith(
                                  color: _role == 2
                                      ? Colors.successPrimaryColor
                                      : _role == 1
                                          ? Colors.warningPrimaryColor
                                          : Colors.blue,
                                ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // thông tin máy chủ
            Card(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Thông tin máy chủ',
                    style: FluentTheme.of(context).typography.subtitle,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Text(
                        'Địa chỉ: ',
                        style: FluentTheme.of(context).typography.body,
                      ),
                      const SizedBox(width: 8),
                      SizedBox(
                        width: 120,
                        child: TextBox(
                          controller: _serverUrlController,
                          readOnly: true,
                          placeholder: 'Chưa cấu hình',
                          style: FluentTheme.of(context).typography.body,
                        ),
                      ),
                      const SizedBox(width: 5),
                      Text(
                        'Trạng thái: ${_isConnected ? 'Đã kết nối' : 'Mất kết nối'}',
                        style:
                            FluentTheme.of(context).typography.body?.copyWith(
                                  color: _isConnected
                                      ? Colors.successPrimaryColor
                                      : Colors.errorPrimaryColor,
                                ),
                      ),
                      if (_lastRequestTime != null) ...[
                        const SizedBox(width: 8),
                        Text(
                          '($_lastRequestTime)',
                          style: FluentTheme.of(context).typography.caption,
                        ),
                      ],
                      const SizedBox(width: 8),
                      IconButton(
                        icon: _isCheckingConnection
                            ? const ProgressRing(strokeWidth: 2)
                            : const Icon(FluentIcons.refresh),
                        onPressed: _isCheckingConnection
                            ? null
                            : _checkServerConnection,
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Thêm thông tin ca thi đã chọn
            _buildSelectedExamInfo(),

            // Stats section
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    context,
                    'Tổng số đề thi',
                    '15',
                    Colors.blue,
                    FluentIcons.page_list,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildStatCard(
                    context,
                    'Thí sinh đã thi',
                    '128',
                    Colors.green,
                    FluentIcons.people,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildStatCard(
                    context,
                    'Tỷ lệ đạt',
                    '85%',
                    Colors.orange,
                    FluentIcons.b_i_dashboard,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            Text(
              'Lịch thi',
              style: FluentTheme.of(context).typography.subtitle,
            ),
            const SizedBox(height: 16),

            if (_isLoading)
              const Center(child: ProgressRing())
            else if (_error != null)
              InfoBar(
                title: Text(_error!),
                severity: InfoBarSeverity.warning,
              )
            else if (_examPeriods.isEmpty)
              const Center(
                child: Text('Không có lịch thi nào được phân công'),
              )
            else
              for (var period in _examPeriods)
                Card(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        period.name,
                        style: FluentTheme.of(context).typography.subtitle,
                      ),
                      Text(
                        'Thời gian: ${_formatDateTime(period.startTime)} - ${_formatDateTime(period.endTime)}',
                      ),
                      const SizedBox(height: 16),
                      for (var shift in period.shifts) ...[
                        ListTile(
                          leading: const Icon(FluentIcons.calendar),
                          title: Text(shift.name),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Thời gian: ${_formatDateTime(shift.startTime)} - ${_formatDateTime(shift.endTime)}',
                              ),
                              for (var room in shift.rooms)
                                Text(
                                  '${room.name} (${room.facility}) - ${room.subject.name}${room.subject.exam != null ? ' - ${room.subject.exam!.name}' : ''}',
                                ),
                            ],
                          ),
                        ),
                        if (shift != period.shifts.last) const Divider(),
                      ],
                    ],
                  ),
                ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(BuildContext context, String title, String value,
      AccentColor color, IconData icon) {
    return Card(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 24),
              const SizedBox(width: 8),
              Text(
                title,
                style: FluentTheme.of(context).typography.body,
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            value,
            style: FluentTheme.of(context)
                .typography
                .titleLarge!
                .copyWith(color: color),
          ),
        ],
      ),
    );
  }

  Widget _buildActivityItem(BuildContext context, String action, String subject,
      String detail, DateTime date) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 40,
          decoration: BoxDecoration(
            color: Colors.successPrimaryColor,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                action,
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              Text(subject),
            ],
          ),
        ),
        Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              detail,
              style: const TextStyle(color: Colors.successPrimaryColor),
            ),
            Text(
              '${date.day}/${date.month}/${date.year}',
              style: const TextStyle(fontSize: 12),
            ),
          ],
        ),
      ],
    );
  }

  String _formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }
}
