import 'package:fluent_ui/fluent_ui.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:window_manager/window_manager.dart';
import 'package:provider/provider.dart';
import 'package:edudex_quiz_client/theme.dart';
import 'package:edudex_quiz_client/screens/dashboard/dashboard_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with WindowListener {
  final _soBaoDanhController = TextEditingController();
  final _maSinhVienController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  final _soBaoDanhFocusNode = FocusNode();
  final _maSinhVienFocusNode = FocusNode();
  final _loginFocusNode = FocusNode();
  String? _errorMessage;
  bool _rememberMe = false;
  bool _isLoading = false;

  @override
  void initState() {
    windowManager.addListener(this);
    super.initState();
  }

  @override
  void dispose() {
    windowManager.removeListener(this);
    _soBaoDanhController.dispose();
    _maSinhVienController.dispose();
    if (_soBaoDanhFocusNode.hasListeners) {
      _soBaoDanhFocusNode.dispose();
    }
    if (_maSinhVienFocusNode.hasListeners) {
      _maSinhVienFocusNode.dispose();
    }
    if (_loginFocusNode.hasListeners) {
      _loginFocusNode.dispose();
    }
    super.dispose();
  }

  Future<void> _login() async {
    if (_soBaoDanhController.text.isEmpty ||
        _maSinhVienController.text.isEmpty) {
      setState(() {
        _errorMessage = 'Vui lòng nhập đầy đủ thông tin!';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final teacherIp = prefs.getString('teacher_ip');

      if (teacherIp == null) {
        throw Exception('Không tìm thấy địa chỉ máy chủ');
      }

      print('🔐 Đang đăng nhập...');
      final url = 'http://$teacherIp:8689/auth/student';
      print('📡 URL: $url');
      print(
          '📝 Params: student_code=${_maSinhVienController.text}, exam_code=${_soBaoDanhController.text}');

      final response = await http.post(
        Uri.parse(url).replace(queryParameters: {
          'student_code': _maSinhVienController.text,
          'exam_code': _soBaoDanhController.text,
        }),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      print('📥 Status code: ${response.statusCode}');
      print('📄 Response: ${response.body}');

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        // Lưu thông tin đăng nhập
        await prefs.setString('token', data['data']['token']);
        await prefs.setString(
            'student_data', json.encode(data['data']['student']));

        if (mounted) {
          Navigator.pushReplacement(
            context,
            FluentPageRoute(builder: (context) => const DashboardScreen()),
          );
        }
      } else {
        setState(() {
          _errorMessage =
              data['message'] ?? 'Thông tin đăng nhập không chính xác!';
        });
        print('❌ Lỗi đăng nhập: ${data['message']}');
      }
    } catch (e) {
      print('❌ Exception: $e');
      setState(() {
        _errorMessage = 'Đã có lỗi xảy ra: ${e.toString()}';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();
    final size = MediaQuery.of(context).size;
    final isSmallScreen = size.width < 900;

    Widget buildLoginForm() {
      return Form(
        key: _formKey,
        child: Container(
          padding: const EdgeInsets.all(48.0),
          constraints: const BoxConstraints(maxWidth: 500),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Center(
                child: Image.asset(
                  'assets/logo.png',
                  width: 120,
                  height: 120,
                ),
              ),
              const SizedBox(height: 32),
              Center(
                child: Text(
                  'Đăng nhập',
                  style: FluentTheme.of(context).typography.titleLarge,
                ),
              ),
              const SizedBox(height: 32),
              InfoLabel(
                label: 'Số báo danh',
                child: TextBox(
                  controller: _soBaoDanhController,
                  placeholder: 'Nhập số báo danh',
                  focusNode: _soBaoDanhFocusNode,
                  onSubmitted: (_) => _maSinhVienFocusNode.requestFocus(),
                ),
              ),
              const SizedBox(height: 16),
              InfoLabel(
                label: 'Mã sinh viên',
                child: TextBox(
                  controller: _maSinhVienController,
                  placeholder: 'Nhập mã sinh viên',
                  focusNode: _maSinhVienFocusNode,
                  onSubmitted: (_) => _login(),
                ),
              ),
              if (_errorMessage != null) ...[
                const SizedBox(height: 24),
                Text(
                  _errorMessage!,
                  style: TextStyle(color: Colors.errorPrimaryColor),
                ),
              ],
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  focusNode: _loginFocusNode,
                  onPressed: _isLoading ? null : _login,
                  child: Padding(
                    padding: const EdgeInsets.all(8.0),
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
                              Text('Đang đăng nhập...'),
                            ],
                          )
                        : const Text('Kiểm tra thông tin'),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    Widget buildIllustration() {
      if (isSmallScreen) return const SizedBox.shrink();

      return Container(
        constraints: const BoxConstraints(maxWidth: 600),
        child: Center(
          child: SvgPicture.asset(
            'assets/login_illustration.svg',
            width: 500,
          ),
        ),
      );
    }

    return NavigationView(
      appBar: NavigationAppBar(
        automaticallyImplyLeading: false,
        title: () {
          return const DragToMoveArea(
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              // child: Text('Đăng nhập'),
            ),
          );
        }(),
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
      content: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0),
        child: isSmallScreen
            ? SingleChildScrollView(
                child: Column(
                  children: [
                    const SizedBox(height: 32),
                    buildIllustration(),
                    buildLoginForm(),
                  ],
                ),
              )
            : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Expanded(
                    child: buildIllustration(),
                  ),
                  Expanded(
                    child: buildLoginForm(),
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
    final FluentThemeData theme = FluentTheme.of(context);

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
