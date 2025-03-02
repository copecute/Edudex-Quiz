import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import 'package:window_manager/window_manager.dart';
import '../theme.dart';
import 'login.dart';
import 'dart:io';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:network_info_plus/network_info_plus.dart';
import '../services/connection_service.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with WindowListener {
  final TextEditingController _ipController = TextEditingController();
  bool _isLoading = false;
  static const String TEACHER_IP_KEY = 'teacher_ip';
  static const int STUDENT_PORT = 8688; // Port cho student
  static const int TEACHER_PORT = 8689; // Port của teacher
  bool _isSearching = false;
  List<String> _foundTeachers = [];
  final ConnectionService _connectionService = ConnectionService();
  bool _isInitializing = true; // Thêm biến theo dõi trạng thái khởi tạo

  @override
  void initState() {
    windowManager.addListener(this);
    super.initState();
    _initialize();
  }

  Future<void> _searchTeachers() async {
    setState(() {
      _isSearching = true;
      _foundTeachers.clear();
    });

    try {
      final commonSubnets = [
        '192.168.1',
        '192.168.0',
        '10.0.0',
        '10.0.1',
        '172.16.0'
      ];

      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => ContentDialog(
          title: Row(
            children: [
              const ProgressRing(strokeWidth: 3),
              const SizedBox(width: 16),
              const Text('Đang tìm kiếm...'),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Vui lòng chờ...'),
            ],
          ),
        ),
      );

      print('🔍 Đang tìm kiếm teacher...');

      for (final subnet in commonSubnets) {
        final futures = <Future>[];
        for (int i = 1; i < 20; i++) {
          final ip = '$subnet.$i';
          futures.add(_checkTeacherClient(ip));
        }
        await Future.wait(futures);

        // Nếu tìm thấy teacher, kết nối ngay
        if (_foundTeachers.isNotEmpty) {
          Navigator.pop(context); // Đóng dialog tìm kiếm
          final teacherIp = _foundTeachers.first;
          _ipController.text = teacherIp;
          await _handleConnect();
          return;
        }
      }

      Navigator.pop(context); // Đóng dialog tìm kiếm

      // Hiển thị kết quả
      if (_foundTeachers.isEmpty) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Không tìm thấy'),
            content: const Text(
              'Không tìm thấy máy giáo viên trong mạng.\n'
              'Vui lòng kiểm tra:\n'
              '• Máy giáo viên đã bật chưa\n'
              '• Máy giáo viên có trong cùng mạng LAN không\n'
              '• Phần mềm giáo viên đã chạy chưa',
              style: TextStyle(height: 1.5),
            ),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(context),
              ),
              FilledButton(
                child: const Text('Thử lại'),
                onPressed: () {
                  Navigator.pop(context);
                  _searchTeachers();
                },
              ),
            ],
          ),
        );
      }
    } catch (e) {
      Navigator.pop(context); // Đóng dialog tìm kiếm
      showDialog(
        context: context,
        builder: (context) => ContentDialog(
          title: const Text('Lỗi'),
          content: Text('Không thể tìm kiếm: ${e.toString()}'),
          actions: [
            Button(
              child: const Text('Đóng'),
              onPressed: () => Navigator.pop(context),
            ),
          ],
        ),
      );
    } finally {
      setState(() {
        _isSearching = false;
      });
    }
  }

  Future<void> _checkTeacherClient(String ip) async {
    try {
      print('🔍 Kiểm tra IP: $ip');
      await _connectionService.connectToTeacher(ip);
      setState(() => _foundTeachers.add(ip));
      print('✅ Tìm thấy teacher tại: $ip');
      // await _connectionService.disconnect();
    } catch (e) {
      // Bỏ qua lỗi kết nối - IP không phải teacher
    }
  }

  Future<void> _handleConnect() async {
    if (_ipController.text.isEmpty) {
      print('❌ Địa chỉ IP trống');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final ip = _ipController.text.trim();
      await _connectionService.connectToTeacher(ip);

      if (mounted) {
        Navigator.pushReplacement(
          context,
          FluentPageRoute(builder: (context) => const LoginScreen()),
        );
      }
    } catch (e) {
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi kết nối'),
            content: SelectableText(
              e.toString().replaceAll('Exception: ', ''),
              style: const TextStyle(height: 1.5),
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
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _initialize() async {
    try {
      setState(() => _isInitializing = true);

      // 1. Khởi tạo TCP server trước
      await _connectionService.startServer();
      print('✅ Đã khởi tạo TCP server');

      // 2. Load saved IP
      final prefs = await SharedPreferences.getInstance();
      final savedIP = prefs.getString(TEACHER_IP_KEY);

      // 3. Delay cho splash screen
      await Future.delayed(const Duration(seconds: 2));

      // 4. Nếu có saved IP thì thử kết nối
      if (savedIP != null) {
        _ipController.text = savedIP;
        print('🔄 Tìm thấy IP teacher đã lưu: $savedIP');
        try {
          await _handleConnect();
          return; // Kết nối thành công thì return luôn
        } catch (e) {
          print('❌ Không thể kết nối tới IP đã lưu: $e');
          // Kết nối thất bại thì tiếp tục tìm kiếm
        }
      }

      // 5. Tự động tìm kiếm teacher
      if (mounted) {
        setState(() =>
            _isInitializing = false); // Tắt loading để hiện giao diện tìm kiếm
        await _searchTeachers(); // Tự động tìm kiếm
      }
    } catch (e) {
      print('❌ Lỗi khởi tạo: $e');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể khởi tạo: $e'),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isInitializing = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isInitializing) {
      return NavigationView(
        content: ScaffoldPage(
          padding: EdgeInsets.zero,
          content: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Image.asset(
                  'assets/logo.png',
                  width: 200,
                  height: 200,
                ),
                const SizedBox(height: 32),
                const ProgressRing(),
                const SizedBox(height: 16),
                const Text(
                  'Đang khởi tạo môi trường...',
                  style: TextStyle(fontSize: 16),
                ),
                const SizedBox(height: 20),
                Text(
                  'EduDex Quiz',
                  style: FluentTheme.of(context).typography.titleLarge,
                ),
              ],
            ),
          ),
        ),
      );
    }

    final appTheme = context.watch<AppTheme>();

    return NavigationView(
      appBar: NavigationAppBar(
        automaticallyImplyLeading: false,
        title: const DragToMoveArea(
          child: Align(
            alignment: AlignmentDirectional.centerStart,
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
      content: ScaffoldPage(
        padding: EdgeInsets.zero,
        content: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset(
                'assets/logo.png',
                width: 200,
                height: 200,
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: 300,
                child: TextBox(
                  controller: _ipController,
                  placeholder: 'Nhập địa chỉ IP của giáo viên',
                ),
              ),
              const SizedBox(height: 20),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  FilledButton(
                    onPressed: _isLoading ? null : _handleConnect,
                    child: _isLoading
                        ? const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              SizedBox(
                                width: 16,
                                height: 16,
                                child: ProgressRing(),
                              ),
                              SizedBox(width: 8),
                              Text('Đang kết nối...'),
                            ],
                          )
                        : const Text('Kết nối'),
                  ),
                  const SizedBox(width: 8),
                  FilledButton(
                    onPressed: _isSearching ? null : _searchTeachers,
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (_isSearching) ...[
                          const ProgressRing(strokeWidth: 2),
                          const SizedBox(width: 8),
                        ],
                        const Icon(FluentIcons.search),
                        const SizedBox(width: 8),
                        Text(_isSearching
                            ? 'Đang tìm kiếm...'
                            : 'Tự động tìm kiếm'),
                      ],
                    ),
                  ),
                ],
              ),
              if (_foundTeachers.isNotEmpty) ...[
                const SizedBox(height: 20),
                const Text('Đã tìm thấy:'),
                const SizedBox(height: 8),
                ...List.generate(
                  _foundTeachers.length,
                  (index) => Button(
                    onPressed: () {
                      _ipController.text = _foundTeachers[index];
                      _handleConnect();
                    },
                    child: Text(_foundTeachers[index]),
                  ),
                ),
              ],
              const SizedBox(height: 20),
              Text(
                'EduDex Quiz',
                style: FluentTheme.of(context).typography.titleLarge,
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _connectionService.dispose();
    windowManager.removeListener(this);
    _ipController.dispose();
    super.dispose();
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
