import 'package:fluent_ui/fluent_ui.dart' hide Page;
import 'package:flutter/foundation.dart';
import 'package:provider/provider.dart';
import 'package:window_manager/window_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;

import '../../theme.dart';
import 'home_page.dart';
import '../settings.dart';
import '../splash_screen.dart';
import '../login.dart';
import 'quiz_management.dart';
import 'student_management.dart';
import 'package:edudex_quiz_teacher_client/screens/dashboard/results.dart';
import 'exam_room_screen.dart';
import '../../services/database_service.dart';
import '../../services/http_server_service.dart';
import '../../services/exam_database_service.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> with WindowListener {
  bool value = false;
  final viewKey = GlobalKey(debugLabel: 'dashboard_view_key');
  int _selectedIndex = 0;

  final List<Widget> _pages = const [
    HomePage(),
    QuizManagementPage(),
    StudentManagementPage(),
    ResultsPage(),
    Settings(),
  ];

  final _pageStorageBucket = PageStorageBucket();
  final _httpService = HttpServerService();
  final _examDb = ExamDatabaseService();

  @override
  void initState() {
    windowManager.addListener(this);
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    windowManager.removeListener(this);
    super.dispose();
  }

  Future<void> _loadData() async {
    final examData = await _examDb.getExamData();
    if (examData == null) {
      // Nếu không có dữ liệu, quay về màn hình login
      if (mounted) {
        Navigator.pushReplacement(
          context,
          FluentPageRoute(builder: (context) => const LoginScreen()),
        );
      }
    }
  }

  Future<void> _handleLogout() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final serverUrl = prefs.getString('server_url');
      final token = prefs.getString('user_token');

      if (serverUrl == null || token == null) {
        print('❌ Không tìm thấy thông tin server hoặc token');
        return;
      }

      final response = await http.post(
        Uri.parse('$serverUrl/api/logout'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'copecute $token',
        },
      );

      print('📥 Status code: ${response.statusCode}');
      print('📦 Response body: ${response.body}');

      // Xóa thông tin người dùng (giữ lại server_url)
      await prefs.remove('user_token');
      await prefs.remove('user_id');
      await prefs.remove('username');
      await prefs.remove('email');
      await prefs.remove('user_role');
      print('🗑️ Đã xóa thông tin người dùng');

      if (!mounted) return;

      // Chuyển về màn hình login thay vì splash screen
      Navigator.pushAndRemoveUntil(
        context,
        FluentPageRoute(builder: (context) => const LoginScreen()),
        (route) => false,
      );
    } catch (e, stackTrace) {
      print('🔥 Lỗi đăng xuất: $e');
      print('📚 Stack trace: $stackTrace');

      if (!mounted) return;

      showDialog(
        context: context,
        builder: (context) => ContentDialog(
          title: const Text('Lỗi'),
          content: const Text('Đã có lỗi xảy ra khi đăng xuất'),
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

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();

    return PageStorage(
      bucket: _pageStorageBucket,
      child: NavigationView(
        key: viewKey,
        appBar: NavigationAppBar(
          automaticallyImplyLeading: false,
          title: const DragToMoveArea(
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              child: Text('EduDex Quiz'),
            ),
          ),
          actions: Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Align(
                alignment: AlignmentDirectional.centerEnd,
                child: Padding(
                  padding: const EdgeInsetsDirectional.only(end: 8.0),
                  child: ToggleSwitch(
                    content: const Text('Chế độ tối'),
                    checked: FluentTheme.of(context).brightness.isDark,
                    onChanged: (v) {
                      if (v) {
                        appTheme.mode = ThemeMode.dark;
                      } else {
                        appTheme.mode = ThemeMode.light;
                      }
                    },
                  ),
                ),
              ),
              const WindowButtons(),
            ],
          ),
        ),
        pane: NavigationPane(
          selected: _selectedIndex,
          onChanged: (index) => setState(() => _selectedIndex = index),
          items: [
            PaneItem(
              icon: const Icon(FluentIcons.home),
              title: const Text('Tổng quan'),
              body: _pages[0],
            ),
            PaneItem(
              icon: const Icon(FluentIcons.page_list),
              title: const Text('Quản lý đề thi'),
              body: _pages[1],
            ),
            PaneItem(
              icon: const Icon(FluentIcons.people),
              title: const Text('Quản lý thí sinh'),
              body: _pages[2],
            ),
            PaneItem(
              icon: const Icon(FluentIcons.room),
              title: const Text('Phòng thi'),
              body: ExamRoomScreen(
                httpService: _httpService,
              ),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.b_i_dashboard),
              title: const Text('Kết quả'),
              body: _pages[3],
            ),
          ],
          footerItems: [
            PaneItemSeparator(),
            PaneItem(
              icon: const Icon(FluentIcons.settings),
              title: const Text('Cài đặt'),
              body: _pages[4],
            ),
            PaneItem(
              icon: const Icon(FluentIcons.sign_out),
              title: const Text('Đăng xuất'),
              body: _pages[0],
              onTap: () {
                showDialog(
                  context: context,
                  builder: (context) => ContentDialog(
                    title: const Text('Xác nhận đăng xuất'),
                    content: const Text(
                        'Bạn có chắc chắn muốn đăng xuất khỏi tài khoản?'),
                    actions: [
                      FilledButton(
                        child: const Text('Có'),
                        onPressed: () {
                          Navigator.pop(context);
                          _handleLogout();
                        },
                      ),
                      Button(
                        child: const Text('Không'),
                        onPressed: () {
                          Navigator.pop(context);
                          setState(() {
                            _selectedIndex = 0;
                          });
                        },
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  @override
  void onWindowClose() async {
    bool isPreventClose = await windowManager.isPreventClose();
    if (isPreventClose && mounted) {
      showDialog(
        context: context,
        builder: (_) {
          return ContentDialog(
            title: const Text('Xác nhận đóng'),
            content: const Text('Bạn có chắc chắn muốn đóng cửa sổ này?'),
            actions: [
              FilledButton(
                child: const Text('Có'),
                onPressed: () async {
                  Navigator.pop(context);
                  await windowManager.setPreventClose(false);
                  await windowManager.close();
                },
              ),
              Button(
                child: const Text('Không'),
                onPressed: () {
                  Navigator.pop(context);
                },
              ),
            ],
          );
        },
      );
    }
  }
}

class WindowButtons extends StatelessWidget {
  const WindowButtons({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = FluentTheme.of(context);
    return SizedBox(
      width: 138,
      height: 50,
      child: WindowCaption(
        brightness: theme.brightness,
        backgroundColor: Colors.transparent,
      ),
    );
  }
}
