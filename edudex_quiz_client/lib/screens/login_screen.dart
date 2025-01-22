import 'package:fluent_ui/fluent_ui.dart';
import 'dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _showPassword = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return NavigationView(
      appBar: const NavigationAppBar(
        title: Text('Edudex Quiz'),
        automaticallyImplyLeading: false,
      ),
      content: const _LoginForm(),
    );
  }
}

class _LoginForm extends StatefulWidget {
  const _LoginForm({Key? key}) : super(key: key);

  @override
  _LoginFormState createState() => _LoginFormState();
}

class _LoginFormState extends State<_LoginForm> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _showPassword = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 400),
        child: Card(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Icon(
                FluentIcons.education,
                size: 64,
                color: Color(0xFF0078D4),
              ),
              const SizedBox(height: 24),
              InfoLabel(
                label: 'Mã sinh viên',
                child: TextBox(
                  controller: _emailController,
                  placeholder: 'Nhập mã sinh viên của bạn',
                  prefix: const Padding(
                    padding: EdgeInsets.only(left: 8.0),
                    child: Icon(FluentIcons.mail),
                  ),
                  onChanged: (value) => setState(() {}),
                ),
              ),
              const SizedBox(height: 16),
              InfoLabel(
                label: 'Mật khẩu',
                child: TextBox(
                  controller: _passwordController,
                  placeholder: 'Nhập mật khẩu của bạn',
                  obscureText: !_showPassword,
                  prefix: const Padding(
                    padding: EdgeInsets.only(left: 8.0),
                    child: Icon(FluentIcons.lock),
                  ),
                  suffix: IconButton(
                    icon: Icon(
                      _showPassword ? FluentIcons.hide : FluentIcons.view,
                    ),
                    onPressed: () {
                      setState(() {
                        _showPassword = !_showPassword;
                      });
                    },
                  ),
                ),
              ),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: _emailController.text.isEmpty ||
                        _passwordController.text.isEmpty
                    ? null
                    : () {
                        Navigator.of(context).pushReplacement(
                          FluentPageRoute(
                            builder: (context) => const DashboardScreen(),
                          ),
                        );
                      },
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: const Text(
                    'Đăng nhập',
                    style: TextStyle(fontSize: 16),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              HyperlinkButton(
                onPressed: () {
                  // TODO: Implement forgot password
                },
                child: const Text('Quên mật khẩu?'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
