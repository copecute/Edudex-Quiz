import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../main.dart'; // Add this import to access ThemeProvider
import '../screens/quiz_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    return NavigationView(
      appBar: NavigationAppBar(
        automaticallyImplyLeading: false,
        title: const Text('Edudex Quiz'),
        actions: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            Padding(
              padding: const EdgeInsets.only(right: 8.0),
              child: IconButton(
                icon: Icon(context.watch<ThemeProvider>().isDark
                    ? FluentIcons.sunny
                    : FluentIcons.clear_night),
                onPressed: () {
                  context.read<ThemeProvider>().toggleTheme();
                },
              ),
            ),
          ],
        ),
      ),
      pane: NavigationPane(
        selected: _selectedIndex,
        onChanged: (index) => setState(() => _selectedIndex = index),
        displayMode: PaneDisplayMode.auto,
        items: [
          PaneItem(
            icon: const Icon(FluentIcons.view_dashboard),
            title: const Text('Tổng quan'),
            body: _buildDashboardContent(),
          ),
          PaneItem(
            icon: const Icon(FluentIcons.user_optional),
            title: const Text('Bài kiểm tra'),
            body: Center(
              child: FilledButton(
                onPressed: () {
                  Navigator.of(context).push(
                    FluentPageRoute(
                      builder: (context) => const QuizScreen(),
                    ),
                  );
                },
                child: const Text('Bắt đầu làm bài'),
              ),
            ),
          ),
          PaneItem(
            icon: const Icon(FluentIcons.education),
            title: const Text('môn học'),
            body: const Center(child: const Text('Danh sách môn học')),
          ),
        ],
        footerItems: [
          PaneItem(
            icon: const Icon(FluentIcons.user_optional),
            title: const Text('Tài khoản'),
            body: const Center(child: const Text('Thông tin tài khoản')),
          ),
          PaneItem(
            icon: const Icon(FluentIcons.settings),
            title: const Text('Cài đặt'),
            body: const Center(child: const Text('Cài đặt hệ thống')),
          ),
        ],
      ),
    );
  }

  Widget _buildDashboardContent() {
    return ScaffoldPage.scrollable(
      children: [
        // Move header inside children
        const Padding(
          padding: EdgeInsets.fromLTRB(24.0, 24.0, 24.0, 0),
          child: Text(
            'Tổng quan',
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(24.0),
          child: LayoutBuilder(
            builder: (context, constraints) {
              bool isWide = constraints.maxWidth > 900;

              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Stats Cards Row
                  Wrap(
                    spacing: 16,
                    runSpacing: 16,
                    children: [
                      _buildStatCard(
                        'Bài kiểm tra',
                        '12',
                        FluentIcons.user_optional,
                        Colors.blue,
                        isWide
                            ? (constraints.maxWidth - 48) / 3
                            : constraints.maxWidth,
                      ),
                      _buildStatCard(
                        'môn học',
                        '5',
                        FluentIcons.education,
                        Colors.orange,
                        isWide
                            ? (constraints.maxWidth - 48) / 3
                            : constraints.maxWidth,
                      ),
                      _buildStatCard(
                        'Điểm trung bình',
                        '8.5',
                        FluentIcons.user_optional,
                        Colors.green,
                        isWide
                            ? (constraints.maxWidth - 48) / 3
                            : constraints.maxWidth,
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Recent Activities
                  Text(
                    'Hoạt động gần đây',
                    style: FluentTheme.of(context).typography.subtitle,
                  ),
                  const SizedBox(height: 16),
                  Card(
                    padding: const EdgeInsets.all(16),
                    child: ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: 5,
                      separatorBuilder: (context, index) => const Divider(),
                      itemBuilder: (context, index) {
                        return ListTile(
                          leading: const Icon(FluentIcons.clock),
                          title: Text('Hoạt động ${5 - index}'),
                          subtitle: Text('Mô tả hoạt động ${5 - index}'),
                          trailing: Text('${index + 1} giờ trước'),
                        );
                      },
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon,
      AccentColor color, double width) {
    return Card(
      padding: const EdgeInsets.all(16),
      child: SizedBox(
        width: width,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.normal,
                  ),
                ),
                Icon(
                  icon,
                  color: color,
                  size: 24,
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
