import 'package:fluent_ui/fluent_ui.dart' hide Page;
import 'package:flutter/foundation.dart';
import 'package:flutter_acrylic/flutter_acrylic.dart' as flutter_acrylic;
import 'package:provider/provider.dart';
import 'package:system_theme/system_theme.dart';
import 'package:window_manager/window_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'screens/splash_screen.dart';
import 'theme.dart';
import 'services/database_service.dart';
import 'services/api_service.dart';
import 'services/tcp_server_service.dart';
import 'providers/exam_provider.dart';
import 'providers/student_provider.dart';
import 'providers/tcp_server_provider.dart';
import 'services/log_service.dart';

const String appTitle = 'EduDex Quiz';

bool get isDesktop {
  if (kIsWeb) return false;
  return [
    TargetPlatform.windows,
    TargetPlatform.linux,
    TargetPlatform.macOS,
  ].contains(defaultTargetPlatform);
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await SharedPreferences.getInstance();

  final appTheme = AppTheme();
  await appTheme.loadSettings(); // Load tất cả cài đặt

  final logService = LogService();
  final databaseService = DatabaseService();
  final apiService = ApiService();
  final tcpService = TcpServerService(
    dbService: databaseService,
    logService: logService,
  );

  if (!kIsWeb &&
      [TargetPlatform.windows, TargetPlatform.android]
          .contains(defaultTargetPlatform)) {
    SystemTheme.accentColor.load();
  }

  if (!kIsWeb && isDesktop) {
    await flutter_acrylic.Window.initialize();
    if (defaultTargetPlatform == TargetPlatform.windows) {
      await flutter_acrylic.Window.hideWindowControls();
    }
    await WindowManager.instance.ensureInitialized();
    windowManager.waitUntilReadyToShow().then((_) async {
      await windowManager.setTitleBarStyle(
        TitleBarStyle.hidden,
        windowButtonVisibility: false,
      );
      await windowManager.setMinimumSize(const Size(500, 600));
      await windowManager.setSize(const Size(1000, 700));
      await windowManager.center();
      await windowManager.maximize();
      await windowManager.show();
      await windowManager.setPreventClose(true);
      await windowManager.setSkipTaskbar(false);
    });
  }

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: appTheme),
        Provider<DatabaseService>.value(value: databaseService),
        Provider<ApiService>.value(value: apiService),
        Provider<TcpServerService>.value(value: tcpService),
        ChangeNotifierProvider(
          create: (context) => ExamProvider(
            dbService: databaseService,
            apiService: apiService,
          ),
        ),
        ChangeNotifierProvider(
          create: (context) => StudentProvider(
            dbService: databaseService,
          ),
        ),
        Provider<LogService>.value(value: logService),
        ChangeNotifierProvider(
          create: (context) => TCPServerProvider(
            tcpService: tcpService,
            dbService: databaseService,
            logService: logService,
          ),
        ),
      ],
      child: const MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();
    return FluentApp(
      title: appTitle,
      themeMode: appTheme.mode,
      debugShowCheckedModeBanner: false,
      color: appTheme.color,
      darkTheme: FluentThemeData(
        brightness: Brightness.dark,
        accentColor: appTheme.color,
        visualDensity: VisualDensity.standard,
        focusTheme: FocusThemeData(
          glowFactor: is10footScreen(context) ? 2.0 : 0.0,
        ),
      ),
      theme: FluentThemeData(
        accentColor: appTheme.color,
        visualDensity: VisualDensity.standard,
        focusTheme: FocusThemeData(
          glowFactor: is10footScreen(context) ? 2.0 : 0.0,
        ),
      ),
      locale: appTheme.locale,
      builder: (context, child) {
        return Directionality(
          textDirection: appTheme.textDirection,
          child: NavigationPaneTheme(
            data: const NavigationPaneThemeData(),
            child: child!,
          ),
        );
      },
      home: const SplashScreen(),
    );
  }
}
