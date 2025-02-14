import 'package:fluent_ui/fluent_ui.dart';
import 'package:edudex_quiz_teacher_client/screens/quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  String _username = '';
  String _email = '';
  int _role = 0;

  @override
  void initState() {
    super.initState();
    _loadUserInfo();
  }

  Future<void> _loadUserInfo() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _username = prefs.getString('username') ?? 'Không xác định';
      _email = prefs.getString('email') ?? 'Không xác định';
      _role = prefs.getInt('user_role') ?? 0;
    });
    print(
        '👤 Loaded user info - Username: $_username, Email: $_email, Role: $_role');
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      padding: const EdgeInsets.all(24.0),
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
                        style: FluentTheme.of(context).typography.body,
                      ),
                    ],
                  ),
                  const Spacer(),
                  FilledButton(
                    child: const Text('Làm bài ngay'),
                    onPressed: () {
                      Navigator.push(
                        context,
                        FluentPageRoute(
                          builder: (context) => const QuizScreen(),
                        ),
                      );
                    },
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
                    'Bài đã làm',
                    '12',
                    Colors.blue,
                    FluentIcons.clipboard_list,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildStatCard(
                    context,
                    'Điểm trung bình',
                    '8.5',
                    Colors.green,
                    FluentIcons.trending12,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildStatCard(
                    context,
                    'Xếp hạng',
                    '5/120',
                    Colors.orange,
                    FluentIcons.trophy2_solid,
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
                    'Bài kiểm tra cuối kỳ',
                    'Cấu trúc dữ liệu & Giải thuật',
                    '9.5',
                    DateTime.now().subtract(const Duration(days: 2)),
                  ),
                  const SizedBox(height: 12),
                  _buildActivityItem(
                    context,
                    'Bài kiểm tra giữa kỳ',
                    'Lập trình Web',
                    '8.0',
                    DateTime.now().subtract(const Duration(days: 5)),
                  ),
                  const SizedBox(height: 12),
                  _buildActivityItem(
                    context,
                    'Bài kiểm tra 15 phút',
                    'Cơ sở dữ liệu',
                    '7.5',
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

  Widget _buildActivityItem(BuildContext context, String title, String subtitle,
      String score, DateTime date) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 40,
          decoration: BoxDecoration(
            color: double.parse(score) >= 5 ? Colors.green : Colors.red,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              Text(subtitle),
            ],
          ),
        ),
        Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              score,
              style: TextStyle(
                color: double.parse(score) >= 5 ? Colors.green : Colors.red,
                fontWeight: FontWeight.bold,
              ),
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

  String _getRoleName(int role) {
    switch (role) {
      case 0:
        return 'Quản trị viên';
      case 1:
        return 'Giáo viên';
      case 2:
        return 'Cán bộ coi thi';
      default:
        return 'Cán bộ coi thi';
    }
  }
}
