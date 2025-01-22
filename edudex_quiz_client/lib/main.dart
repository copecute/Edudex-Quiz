import 'package:fluent_ui/fluent_ui.dart';
import 'screens/login_screen.dart';
import 'package:provider/provider.dart';
import 'package:window_manager/window_manager.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  try {
    // Chỉ khởi tạo window_manager khi là Windows
    if (const bool.fromEnvironment('dart.library.io')) {
      await windowManager.ensureInitialized();

      WindowOptions windowOptions = const WindowOptions(
        size: Size(1280, 720),
        center: true,
        backgroundColor: Colors.transparent,
        skipTaskbar: false,
        titleBarStyle: TitleBarStyle.normal,
      );

      await windowManager.waitUntilReadyToShow(windowOptions, () async {
        await windowManager.show();
        await windowManager.focus();
      });
    }
  } catch (e) {
    // Bỏ qua lỗi khi chạy trên web
  }

  runApp(
    ChangeNotifierProvider(
      create: (_) => ThemeProvider(),
      child: const MyApp(),
    ),
  );
}

class ThemeProvider extends ChangeNotifier {
  bool isDark = false;

  void toggleTheme() {
    isDark = !isDark;
    notifyListeners();
  }
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<ThemeProvider>(
      builder: (context, themeProvider, child) {
        return FluentApp(
          title: 'Edudex Quiz',
          debugShowCheckedModeBanner: false,
          theme: FluentThemeData(
            brightness:
                themeProvider.isDark ? Brightness.dark : Brightness.light,
            accentColor: Colors.blue,
          ),
          home: const LoginScreen(),
        );
      },
    );
  }
}
