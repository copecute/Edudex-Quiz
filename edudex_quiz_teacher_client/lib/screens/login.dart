import 'package:fluent_ui/fluent_ui.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:window_manager/window_manager.dart';
import 'package:provider/provider.dart';
import '../theme.dart';
import 'package:edudex_quiz_teacher_client/screens/dashboard/dashboard_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/exam_schedule.dart';
import '../widgets/exam_period_selector.dart';

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
  static const String TOKEN_KEY = 'user_token';
  static const String USER_ID_KEY = 'user_id';
  static const String USERNAME_KEY = 'username';
  static const String EMAIL_KEY = 'email';
  static const String ROLE_KEY = 'user_role';
  static const String SERVER_URL_KEY = 'server_url';
  bool _isLoading = false;
  bool _obscurePassword = true;

  // Define the constants for SharedPreferences keys
  static const String SELECTED_PERIOD_ID = 'selected_period_id';
  static const String SELECTED_SHIFT_ID = 'selected_shift_id';
  static const String SELECTED_ROOM_ID = 'selected_room_id';
  static const String SELECTED_SUBJECT_ID = 'selected_subject_id';
  static const String SELECTED_EXAM_ID = 'selected_exam_id';

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
    setState(() {
      _errorMessage = null;
      _isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final serverUrl = prefs.getString('server_url');

      if (serverUrl == null) {
        throw Exception('Không tìm thấy địa chỉ máy chủ');
      }

      final response = await http.post(
        Uri.parse('$serverUrl/api/login'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          'username': _soBaoDanhController.text,
          'password': _maSinhVienController.text,
        }),
      );

      final data = json.decode(response.body);

      if (data['status'] == 'success') {
        // Lưu thông tin người dùng
        await prefs.setString(TOKEN_KEY, data['data']['token']);
        await prefs.setInt(USER_ID_KEY, data['data']['user']['id']);
        await prefs.setString(USERNAME_KEY, data['data']['user']['username']);
        await prefs.setString(EMAIL_KEY, data['data']['user']['email']);
        await prefs.setInt(ROLE_KEY, data['data']['user']['role']);

        // Lưu thông tin cá nhân
        final userInfo = data['data']['info'];
        await prefs.setString('full_name', userInfo['full_name']);
        await prefs.setString('date_of_birth', userInfo['date_of_birth']);
        await prefs.setBool('gender', userInfo['gender']);
        await prefs.setString('phone', userInfo['phone']);
        await prefs.setString('address', userInfo['address']);
        if (userInfo['avatar'] != null) {
          await prefs.setString('avatar', userInfo['avatar']);
        }

        // Lưu thông tin lịch thi
        final scheduleData = json.encode(data['data']['schedule']);
        await prefs.setString('exam_schedule', scheduleData);

        // Hiển thị dialog chọn ca thi
        if (!mounted) return;

        final examPeriods = (data['data']['schedule'] as List)
            .map((period) => ExamPeriod.fromJson(period))
            .toList();

        await showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => ExamPeriodSelector(
            examPeriods: examPeriods,
            onSelected: (period, shift, room) async {
              await prefs.setInt(SELECTED_PERIOD_ID, period.id ?? 0);
              await prefs.setInt(SELECTED_SHIFT_ID, shift.id ?? 0);
              await prefs.setInt(SELECTED_ROOM_ID, room.id ?? 0);
              if (room.subject.id != null) {
                await prefs.setInt(SELECTED_SUBJECT_ID, room.subject.id!);
              }
              if (room.subject.exam?.id != null) {
                await prefs.setInt(SELECTED_EXAM_ID, room.subject.exam!.id!);
              }

              if (!mounted) return;
              Navigator.pushReplacement(
                context,
                FluentPageRoute(builder: (context) => const DashboardScreen()),
              );
            },
          ),
        );
      } else {
        setState(() {
          _errorMessage = data['message'] ?? 'Đăng nhập thất bại';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Lỗi đăng nhập: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _handleExamPeriodSelected(
      ExamPeriod period, ExamShift shift, ExamRoom room) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('selected_shift_id', shift.id ?? 0);
    await prefs.setInt('selected_room_id', room.id ?? 0);

    if (mounted) {
      Navigator.pushReplacement(
        context,
        FluentPageRoute(
          builder: (context) => const DashboardScreen(),
        ),
      );
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
                label: 'Tên tài khoản',
                child: TextBox(
                  controller: _soBaoDanhController,
                  placeholder: 'Nhập tên tài khoản',
                  focusNode: _soBaoDanhFocusNode,
                  onSubmitted: (_) => _maSinhVienFocusNode.requestFocus(),
                ),
              ),
              const SizedBox(height: 16),
              InfoLabel(
                label: 'Mật khẩu',
                child: TextBox(
                  controller: _maSinhVienController,
                  placeholder: 'Nhập mật khẩu',
                  focusNode: _maSinhVienFocusNode,
                  onSubmitted: (_) => _login(),
                  obscureText: _obscurePassword,
                  suffix: IconButton(
                    icon: Icon(
                      _obscurePassword
                          ? FluentIcons.hide3
                          : FluentIcons.red_eye,
                      size: 16,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscurePassword = !_obscurePassword;
                      });
                    },
                  ),
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
                        : const Text('Đăng nhập'),
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
          child: Image.asset(
            'assets/login_illustration.png',
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
