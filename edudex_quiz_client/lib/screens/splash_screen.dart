import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import 'package:window_manager/window_manager.dart';
import '../theme.dart';
import 'login.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with WindowListener {
  final TextEditingController _codeController = TextEditingController();
  bool _isLoading = false;
  static const String SERVER_URL_KEY = 'server_url';

  String _formatServerUrl(String url) {
    String formattedUrl = url.trim();

    // Loại bỏ dấu "/" ở cuối nếu có
    while (formattedUrl.endsWith('/')) {
      formattedUrl = formattedUrl.substring(0, formattedUrl.length - 1);
    }

    // Thêm https:// nếu chưa có protocol
    if (!formattedUrl.startsWith('http://') &&
        !formattedUrl.startsWith('https://')) {
      formattedUrl = 'https://$formattedUrl';
      print('🔒 Tự động thêm https:// vào URL');
    }

    return '$formattedUrl/api/wfaE0FbQWldGoDlGFyFgKWY0MiUizH2';
  }

  @override
  void initState() {
    windowManager.addListener(this);
    super.initState();
    _loadSavedServerUrl();
  }

  Future<void> _loadSavedServerUrl() async {
    final prefs = await SharedPreferences.getInstance();
    final savedUrl = prefs.getString(SERVER_URL_KEY);
    if (savedUrl != null) {
      setState(() {
        _codeController.text = savedUrl;
      });
      print('🔄 Tìm thấy địa chỉ server đã lưu: $savedUrl');
      // Tự động kết nối
      await _handleSubmit();
    } else {
      print('❌ Không tìm thấy địa chỉ server đã lưu');
    }
  }

  Future<void> _saveServerUrl(String url) async {
    String cleanUrl = url.trim();

    // Loại bỏ dấu "/" ở cuối nếu có
    while (cleanUrl.endsWith('/')) {
      cleanUrl = cleanUrl.substring(0, cleanUrl.length - 1);
    }

    // Thêm https:// nếu chưa có protocol khi lưu
    if (!cleanUrl.startsWith('http://') && !cleanUrl.startsWith('https://')) {
      cleanUrl = 'https://$cleanUrl';
      print('🔒 Tự động thêm https:// trước khi lưu URL');
    }

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(SERVER_URL_KEY, cleanUrl);
    print('💾 Đã lưu địa chỉ server (đã clean): $cleanUrl');
  }

  Future<void> _handleSubmit() async {
    if (_codeController.text.isEmpty) {
      print('❌ Địa chỉ máy chủ trống');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      String baseUrl = _codeController.text.trim();
      while (baseUrl.endsWith('/')) {
        baseUrl = baseUrl.substring(0, baseUrl.length - 1);
      }

      // Loại bỏ protocol nếu có
      if (baseUrl.startsWith('http://')) {
        baseUrl = baseUrl.substring(7);
      } else if (baseUrl.startsWith('https://')) {
        baseUrl = baseUrl.substring(8);
      }

      print('🌐 Thử kết nối tới server: $baseUrl');

      // Thử HTTPS trước
      try {
        final httpsUrl = 'https://$baseUrl/api/wfaE0FbQWldGoDlGFyFgKWY0MiUizH2';
        print('🔒 Thử kết nối HTTPS: $httpsUrl');

        final response = await http.post(
          Uri.parse(httpsUrl),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        );

        if (response.statusCode == 200) {
          print('✅ Kết nối HTTPS thành công');
          await _handleResponse(response, 'https://$baseUrl');
          return;
        }
      } catch (e) {
        print('⚠️ Kết nối HTTPS thất bại: $e');
      }

      // Nếu HTTPS thất bại, thử HTTP
      try {
        final httpUrl = 'http://$baseUrl/api/wfaE0FbQWldGoDlGFyFgKWY0MiUizH2';
        print('🔓 Thử kết nối HTTP: $httpUrl');

        final response = await http.post(
          Uri.parse(httpUrl),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        );

        if (response.statusCode == 200) {
          print('✅ Kết nối HTTP thành công');
          await _handleResponse(response, 'http://$baseUrl');
          return;
        }
      } catch (e) {
        print('⚠️ Kết nối HTTP thất bại: $e');
      }

      // Nếu cả hai đều thất bại
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: const Text('Không thể kết nối đến máy chủ'),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    } catch (e, stackTrace) {
      print('🔥 Lỗi: $e');
      print('📚 Stack trace: $stackTrace');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: const Text('Đã có lỗi xảy ra khi kết nối đến máy chủ'),
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
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _handleResponse(http.Response response, String baseUrl) async {
    final data = json.decode(response.body);
    print('✅ Parsed data: $data');

    if (data['messages'] == 'copecute is beautiful') {
      print('✨ Xác thực server thành công');
      await _saveServerUrl(baseUrl);
      print('💾 Đã lưu địa chỉ server: $baseUrl');
      if (mounted) {
        Navigator.pushReplacement(
          context,
          FluentPageRoute(builder: (context) => const LoginScreen()),
        );
      }
    } else {
      print('❌ Message không hợp lệ: ${data['messages']}');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: const Text('Máy chủ không hợp lệ'),
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
            // child: Text('EduDex Quiz'),
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
                  controller: _codeController,
                  placeholder: 'Nhập địa chỉ máy chủ',
                ),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: _isLoading ? null : _handleSubmit,
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
                    : const Text('Xác nhận'),
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

  @override
  void dispose() {
    windowManager.removeListener(this);
    _codeController.dispose();
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
