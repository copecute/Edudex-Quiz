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
  final _connectionService = ConnectionService();

  @override
  void initState() {
    windowManager.addListener(this);
    super.initState();
    _loadSavedTeacherIP();

    // Khi test local, tự động điền localhost
    if (Platform.isWindows) {
      _ipController.text = '127.0.0.1';
    }
  }

  Future<void> _loadSavedTeacherIP() async {
    final prefs = await SharedPreferences.getInstance();
    final savedIP = prefs.getString(TEACHER_IP_KEY);
    if (savedIP != null) {
      setState(() {
        _ipController.text = savedIP;
      });
      print('🔄 Tìm thấy IP teacher đã lưu: $savedIP');
      // Tự động kết nối
      await _handleConnect();
    } else {
      print('❌ Không tìm thấy IP teacher đã lưu');
    }
  }

  Future<void> _saveTeacherIP(String ip) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(TEACHER_IP_KEY, ip);
    print('💾 Đã lưu IP teacher: $ip');
  }

  Future<void> _searchTeachers() async {
    setState(() {
      _isSearching = true;
      _foundTeachers.clear();
    });

    try {
      // Danh sách các IP phổ biến trong mạng LAN
      final commonSubnets = [
        '192.168.1',
        '192.168.0',
        '10.0.0',
        '10.0.1',
        '172.16.0'
      ];

      print('🔍 Đang tìm kiếm teacher...');

      // Quét các IP phổ biến
      for (final subnet in commonSubnets) {
        final futures = <Future>[];
        // Chỉ quét một số IP phổ biến để tăng tốc độ
        for (int i = 1; i < 20; i++) {
          final ip = '$subnet.$i';
          futures.add(_checkTeacherClient(ip));
        }
        await Future.wait(futures);
      }
    } catch (e) {
      print('❌ Lỗi khi tìm kiếm: $e');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể tìm kiếm teacher: ${e.toString()}'),
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
        setState(() {
          _isSearching = false;
        });
      }
    }
  }

  Future<void> _checkTeacherClient(String ip) async {
    try {
      print('🔍 Kiểm tra IP: $ip');
      await _connectionService.connectToTeacher(ip);
      setState(() => _foundTeachers.add(ip));
      print('✅ Tìm thấy teacher tại: $ip');
      await _connectionService.disconnect();
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
      await _saveTeacherIP(ip);

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

  @override
  Widget build(BuildContext context) {
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
                  Button(
                    onPressed: _isSearching ? null : _searchTeachers,
                    child: _isSearching
                        ? const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              SizedBox(
                                width: 16,
                                height: 16,
                                child: ProgressRing(),
                              ),
                              SizedBox(width: 8),
                              Text('Đang tìm...'),
                            ],
                          )
                        : const Text('Tìm tự động'),
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
