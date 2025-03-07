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
  String _fullName = '';
  Map<String, dynamic>? _userInfo;

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
    _loadSelectedExamInfo();
  }

  @override
  void dispose() {
    _serverUrlController.dispose();
    super.dispose();
  }

  Future<void> _loadUserInfo() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _fullName = prefs.getString('full_name') ?? 'Không xác định';

      // Load user info
      _userInfo = {
        'full_name': prefs.getString('full_name'),
        'date_of_birth': prefs.getString('date_of_birth'),
        'gender': prefs.getBool('gender'),
        'phone': prefs.getString('phone'),
        'address': prefs.getString('address'),
        'avatar': prefs.getString('avatar'),
        'email': prefs.getString('email'),
        'role': prefs.getInt('user_role'),
      };
    });
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

  Future<void> _loadSelectedExamInfo() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();

      // Load thông tin lịch thi từ SharedPreferences
      final scheduleData = prefs.getString('exam_schedule');
      if (scheduleData == null) {
        throw Exception('Không tìm thấy thông tin lịch thi');
      }

      final scheduleJson = json.decode(scheduleData) as List;
      _examPeriods =
          scheduleJson.map((period) => ExamPeriod.fromJson(period)).toList();

      // Lấy thông tin đã chọn từ SharedPreferences
      final selectedPeriodId = prefs.getInt('selected_period_id');
      final selectedShiftId = prefs.getInt('selected_shift_id');
      final selectedRoomId = prefs.getInt('selected_room_id');

      if (selectedPeriodId == null ||
          selectedShiftId == null ||
          selectedRoomId == null) {
        throw Exception('Không tìm thấy thông tin ca thi đã chọn');
      }

      // Hiển thị thông tin đã lưu
      setState(() {
        _selectedPeriod = _examPeriods.firstWhere(
          (p) => p.id == selectedPeriodId,
          orElse: () => throw Exception('Không tìm thấy kỳ thi đã chọn'),
        );

        if (_selectedPeriod != null) {
          _selectedShift = _selectedPeriod!.shifts.firstWhere(
            (s) => s.id == selectedShiftId,
            orElse: () => throw Exception('Không tìm thấy ca thi đã chọn'),
          );

          if (_selectedShift != null) {
            _selectedRoom = _selectedShift!.rooms.firstWhere(
              (r) => r.id == selectedRoomId,
              orElse: () => throw Exception('Không tìm thấy phòng thi đã chọn'),
            );
          }
        }
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
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

  void _showUserInfoDialog() {
    showDialog(
      context: context,
      builder: (context) => ContentDialog(
        title: Row(
          children: [
            const Icon(FluentIcons.contact_info),
            const SizedBox(width: 8),
            const Text('Thông tin cán bộ coi thi'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildDetailRow('Họ và tên:', _userInfo?['full_name']),
            _buildDetailRow(
              'Ngày sinh:',
              _formatDate(_userInfo?['date_of_birth']),
            ),
            _buildDetailRow(
              'Giới tính:',
              _userInfo?['gender'] == true ? 'Nam' : 'Nữ',
            ),
            _buildDetailRow('Số điện thoại:', _userInfo?['phone']),
            _buildDetailRow('Email:', _userInfo?['email']),
            _buildDetailRow('Địa chỉ:', _userInfo?['address']),
            _buildDetailRow(
              'Vai trò:',
              _getRoleName(_userInfo?['role'] ?? 0),
            ),
          ],
        ),
        actions: [
          Button(
            child: const Text('Đóng'),
            onPressed: () => Navigator.pop(context),
          ),
        ],
      ),
    );
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return 'N/A';
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      content: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: _isLoading
              ? const Center(child: ProgressRing())
              : _error != null
                  ? InfoBar(
                      title: Text(_error!),
                      severity: InfoBarSeverity.error,
                    )
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Welcome Section
                        Container(
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                FluentTheme.of(context).accentColor,
                                FluentTheme.of(context)
                                    .accentColor
                                    .withOpacity(0.7),
                              ],
                            ),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          padding: const EdgeInsets.all(24.0),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withOpacity(0.2),
                                          borderRadius:
                                              BorderRadius.circular(50),
                                        ),
                                        child: const Icon(
                                          FluentIcons.user_window,
                                          size: 24,
                                          color: Colors.white,
                                        ),
                                      ),
                                      const SizedBox(width: 16),
                                      Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            'Xin chào,',
                                            style: FluentTheme.of(context)
                                                .typography
                                                .body!
                                                .copyWith(color: Colors.white),
                                          ),
                                          Text(
                                            _fullName,
                                            style: FluentTheme.of(context)
                                                .typography
                                                .title!
                                                .copyWith(color: Colors.white),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              FilledButton(
                                style: ButtonStyle(
                                  backgroundColor:
                                      ButtonState.resolveWith((states) {
                                    if (states.isHovering) {
                                      return Colors.white.withOpacity(0.3);
                                    }
                                    return Colors.white.withOpacity(0.2);
                                  }),
                                  padding: ButtonState.all(
                                    const EdgeInsets.symmetric(
                                      horizontal: 16,
                                      vertical: 8,
                                    ),
                                  ),
                                  shape: ButtonState.all(
                                    RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                  ),
                                ),
                                onPressed: _showUserInfoDialog,
                                child: Row(
                                  children: [
                                    const Icon(
                                      FluentIcons.contact_info,
                                      size: 16,
                                      color: Colors.white,
                                    ),
                                    const SizedBox(width: 8),
                                    const Text(
                                      'Thông tin cán bộ',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),
                        // Exam Info Section
                        Text(
                          'Thông tin ca thi',
                          style: FluentTheme.of(context).typography.subtitle,
                        ),
                        const SizedBox(height: 12),
                        Card(
                          padding: const EdgeInsets.all(0),
                          child: Container(
                            decoration: BoxDecoration(
                              border: Border.all(
                                color: FluentTheme.of(context)
                                    .resources
                                    .dividerStrokeColorDefault,
                              ),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                _buildInfoSection(
                                  'Kỳ thi',
                                  [
                                    if (_selectedPeriod != null) ...[
                                      _buildInfoTile(
                                        FluentIcons.calendar,
                                        'Tên kỳ thi',
                                        _selectedPeriod!.name,
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.timer,
                                        'Thời gian',
                                        '${_formatDateTime(_selectedPeriod!.startTime)} - ${_formatDateTime(_selectedPeriod!.endTime)}',
                                      ),
                                    ],
                                  ],
                                ),
                                _buildDivider(),
                                _buildInfoSection(
                                  'Ca thi',
                                  [
                                    if (_selectedShift != null) ...[
                                      _buildInfoTile(
                                        FluentIcons.event,
                                        'Tên ca thi',
                                        _selectedShift!.name,
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.clock,
                                        'Thời gian',
                                        '${_formatDateTime(_selectedShift!.startTime)} - ${_formatDateTime(_selectedShift!.endTime)}',
                                      ),
                                    ],
                                  ],
                                ),
                                _buildDivider(),
                                _buildInfoSection(
                                  'Phòng thi',
                                  [
                                    if (_selectedRoom != null) ...[
                                      _buildInfoTile(
                                        FluentIcons.room,
                                        'Phòng',
                                        _selectedRoom!.name,
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.home,
                                        'Cơ sở',
                                        _selectedRoom!.facility,
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.people_external_share,
                                        'Sức chứa',
                                        '${_selectedRoom!.capacity ?? 0} máy',
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.education,
                                        'Môn thi',
                                        _selectedRoom!.subject.name,
                                      ),
                                    ],
                                  ],
                                ),
                                if (_selectedRoom?.subject.exam != null) ...[
                                  _buildDivider(),
                                  _buildInfoSection(
                                    'Đề thi',
                                    [
                                      _buildInfoTile(
                                        FluentIcons.account_activity,
                                        'Tên đề',
                                        _selectedRoom!.subject.exam!.name ??
                                            'N/A',
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.timer,
                                        'Thời gian làm bài',
                                        '${_selectedRoom!.subject.exam!.duration ?? 0} phút',
                                      ),
                                      _buildInfoTile(
                                        FluentIcons.list,
                                        'Số câu hỏi',
                                        '${_selectedRoom!.subject.exam!.totalQuestions ?? 0} câu',
                                      ),
                                    ],
                                  ),
                                ],
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

  Widget _buildInfoSection(String title, List<Widget> children) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInfoTile(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Icon(icon, size: 16),
          const SizedBox(width: 12),
          SizedBox(
            width: 120,
            child: Text(label),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDivider() {
    return const Divider(
      style: DividerThemeData(
        horizontalMargin: EdgeInsets.zero,
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }

  String _formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }
}
