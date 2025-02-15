import 'package:fluent_ui/fluent_ui.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

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

  @override
  void initState() {
    super.initState();
    _loadUserInfo();
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
                        'Vai trò: Giáo viên',
                        style: FluentTheme.of(context).typography.body,
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

            // Recent activities
            Text(
              'Hoạt động gần đây',
              style: FluentTheme.of(context).typography.subtitle,
            ),
            const SizedBox(height: 16),
            Card(
              padding: const EdgeInsets.all(16),
              child: ListView(
                shrinkWrap: true,
                children: [
                  _buildActivityItem(
                    context,
                    'Tạo đề thi mới',
                    'Cấu trúc dữ liệu & Giải thuật',
                    'Đề thi cuối kỳ',
                    DateTime.now().subtract(const Duration(days: 2)),
                  ),
                  const SizedBox(height: 12),
                  _buildActivityItem(
                    context,
                    'Cập nhật đề thi',
                    'Lập trình Web',
                    'Thêm 5 câu hỏi mới',
                    DateTime.now().subtract(const Duration(days: 5)),
                  ),
                  const SizedBox(height: 12),
                  _buildActivityItem(
                    context,
                    'Xuất kết quả thi',
                    'Cơ sở dữ liệu',
                    '32 thí sinh',
                    DateTime.now().subtract(const Duration(days: 7)),
                  ),
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
}
