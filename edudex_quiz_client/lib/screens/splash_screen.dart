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
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'settings.dart';
import 'package:flutter/services.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with WindowListener {
  final TextEditingController _ipController = TextEditingController();
  final TextEditingController _soMayController = TextEditingController();
  bool _isLoading = false;
  static const String TEACHER_IP_KEY = 'teacher_ip';
  static const String MAY_SO_KEY = 'may_so';
  static const int STUDENT_PORT = 8688; // Port cho student
  static const int TEACHER_PORT = 8689; // Port của teacher
  bool _isSearching = false;
  List<String> _foundTeachers = [];
  final ConnectionService _connectionService = ConnectionService();
  bool _isInitializing = true; // Thêm biến theo dõi trạng thái khởi tạo
  String? _errorMessage;

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
      // Lấy IP của máy hiện tại
      final interfaces = await NetworkInterface.list();
      final localIP = interfaces
          .expand((interface) => interface.addresses)
          .firstWhere((addr) => addr.type == InternetAddressType.IPv4)
          .address;

      print('🖥️ IP máy hiện tại: $localIP');

      // Lấy subnet từ IP hiện tại (vd: 192.168.1)
      final subnet = localIP.substring(0, localIP.lastIndexOf('.'));
      final currentLastOctet = int.parse(localIP.split('.').last);

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
              Text('Đang quét mạng $subnet.*'),
              const SizedBox(height: 8),
              Text('Vui lòng chờ...'),
            ],
          ),
        ),
      );

      print('🔍 Đang quét subnet: $subnet.*');

      // Quét từ 1-255, bỏ qua IP của máy hiện tại
      final futures = <Future>[];
      for (int i = 1; i <= 255; i++) {
        final ip = '$subnet.$i';
        futures.add(_checkTeacherClient(ip));
      }

      // Đợi tất cả các request hoàn thành
      await Future.wait(futures);

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
              '• Máy giáo viên có trong cùng mạng LAN không',
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
      } else {
        // Nếu tìm thấy teacher, kết nối ngay với IP đầu tiên
        final teacherIp = _foundTeachers.first;
        _ipController.text = teacherIp;
        await _handleConnect();
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

  Future<bool> _checkTeacherClient(String ip) async {
    try {
      // lấy số máy đã lưu
      final prefs = await SharedPreferences.getInstance();
      final maySo = prefs.getString(MAY_SO_KEY);

      if (maySo == null) {
        print('❌ Chưa có thông tin số máy');
        return false;
      }

      print('🔍 Kiểm tra teacher tại: $ip với số máy: $maySo');

      final response = await http.post(
        Uri.parse('http://$ip:$TEACHER_PORT/is-teacher').replace(
          queryParameters: {'may': maySo},
        ),
        headers: {'Content-Type': 'application/json'},
      ).timeout(const Duration(seconds: 2));

      print('📥 Response từ $ip: ${response.statusCode} - ${response.body}');

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['status'] == 'success') {
          print('✅ Tìm thấy teacher tại: $ip');
          setState(() {
            if (!_foundTeachers.contains(ip)) {
              _foundTeachers.add(ip);
            }
          });
          return true;
        }
      }
      return false;
    } catch (e) {
      // Bỏ qua lỗi timeout và connection refused
      if (e is TimeoutException || e is SocketException) {
        return false;
      }
      print('❌ Lỗi khi kiểm tra $ip: $e');
      return false;
    }
  }

  Future<void> _handleConnect() async {
    if (_ipController.text.isEmpty) {
      setState(() {
        _errorMessage = 'Vui lòng nhập địa chỉ IP của giáo viên!';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      // lấy số máy đã lưu
      final prefs = await SharedPreferences.getInstance();
      final maySo = prefs.getString(MAY_SO_KEY);

      if (maySo == null) {
        throw Exception('Không tìm thấy thông tin số máy');
      }

      final teacherIp = _ipController.text.trim();
      final url = 'http://$teacherIp:$TEACHER_PORT/is-teacher';
      print('🔄 Đang kết nối tới: $url');

      final response = await http.post(
        Uri.parse(url).replace(
          queryParameters: {'may': maySo},
        ),
        headers: {'Content-Type': 'application/json'},
      ).timeout(
        const Duration(seconds: 5), // thêm timeout 5 giây
        onTimeout: () {
          throw TimeoutException('Kết nối tới máy giáo viên quá thời gian chờ');
        },
      );

      print('📥 Status code: ${response.statusCode}');
      print('📄 Response: ${response.body}');

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        // Lưu thông tin kết nối
        await prefs.setString(TEACHER_IP_KEY, teacherIp);

        if (mounted) {
          Navigator.pushReplacement(
            context,
            FluentPageRoute(builder: (context) => const LoginScreen()),
          );
        }
      } else {
        setState(() {
          _errorMessage =
              data['message'] ?? 'Không thể kết nối tới máy giáo viên';
        });
      }
    } on SocketException catch (e) {
      print('❌ Socket Exception: $e');
      setState(() {
        _errorMessage =
            'Không thể kết nối tới máy giáo viên. Vui lòng kiểm tra:\n'
            '• Địa chỉ IP đã đúng chưa\n'
            '• Máy giáo viên đã bật chưa\n'
            '• Máy giáo viên có trong cùng mạng LAN không';
      });
    } on TimeoutException catch (e) {
      print('❌ Timeout Exception: $e');
      setState(() {
        _errorMessage = 'Kết nối tới máy giáo viên quá thời gian chờ.\n'
            'Vui lòng thử lại sau.';
      });
    } catch (e) {
      print('❌ Exception: $e');
      setState(() {
        _errorMessage = 'Lỗi kết nối: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _initialize() async {
    try {
      setState(() => _isInitializing = true);

      // 1. Kiểm tra xem đã có số máy chưa
      final prefs = await SharedPreferences.getInstance();
      final savedMaySo = prefs.getString(MAY_SO_KEY);

      if (savedMaySo == null) {
        // Nếu chưa có số máy, hiển thị form nhập số máy
        setState(() => _isInitializing = false);
        return;
      }

      // Nếu đã có số máy, tiếp tục khởi tạo
      _soMayController.text = savedMaySo;

      // 2. Khởi tạo TCP server
      await _connectionService.startServer();
      print('✅ Đã khởi tạo TCP server');

      // 3. Load saved IP
      final savedIP = prefs.getString(TEACHER_IP_KEY);

      // 4. Delay cho splash screen
      await Future.delayed(const Duration(seconds: 2));

      // 5. Nếu có saved IP thì thử kết nối
      if (savedIP != null) {
        _ipController.text = savedIP;
        print('🔄 Tìm thấy IP teacher đã lưu: $savedIP');
        try {
          await _handleConnect();
          return;
        } catch (e) {
          print('❌ Không thể kết nối tới IP đã lưu: $e');
        }
      }

      // 6. Tự động tìm kiếm teacher
      if (mounted) {
        setState(() => _isInitializing = false);
        await _searchTeachers();
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

  // Thêm hàm lưu số máy
  Future<void> _saveMaySo() async {
    if (_soMayController.text.isEmpty) {
      setState(() {
        _errorMessage = 'Vui lòng nhập số máy!';
      });
      return;
    }

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(MAY_SO_KEY, _soMayController.text);

      // Sau khi lưu số máy, tiếp tục khởi tạo
      await _initialize();
    } catch (e) {
      setState(() {
        _errorMessage = 'Lỗi khi lưu số máy: ${e.toString()}';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();

    // Màn hình nhập số máy
    if (!_isInitializing && !_soMayController.text.isNotEmpty) {
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
            children: const [WindowButtons()],
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
                const Text(
                  'Nhập số máy của bạn',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: 300,
                  child: TextBox(
                    controller: _soMayController,
                    placeholder: 'Nhập số máy',
                    onSubmitted: (_) => _saveMaySo(),
                    // chỉ cho phép nhập số từ 1-99
                    keyboardType: TextInputType.number,
                    inputFormatters: [
                      FilteringTextInputFormatter.digitsOnly,
                    ],
                    onChanged: (value) {
                      if (value.isNotEmpty) {
                        final number = int.tryParse(value);
                        if (number == null || number < 1 || number > 99) {
                          _soMayController.text = '';
                        }
                      }
                    },
                  ),
                ),
                if (_errorMessage != null) ...[
                  const SizedBox(height: 10),
                  Text(
                    _errorMessage!,
                    style: TextStyle(color: Colors.red),
                  ),
                ],
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: _saveMaySo,
                  child: const Text('Xác nhận'),
                ),
              ],
            ),
          ),
        ),
      );
    }

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
            IconButton(
              icon: const Icon(FluentIcons.settings),
              onPressed: () {
                Navigator.push(
                  context,
                  FluentPageRoute(
                    builder: (context) => const Settings(
                      showBackButton: true,
                      showDisconnectButton: false,
                    ),
                  ),
                );
              },
            ),
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
              if (_errorMessage != null) ...[
                const SizedBox(height: 10),
                Text(
                  _errorMessage!,
                  style: TextStyle(color: Colors.red),
                ),
              ],
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
    _soMayController.dispose();
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
