import 'package:fluent_ui/fluent_ui.dart';
import 'package:window_manager/window_manager.dart';
import 'package:provider/provider.dart';
import '../theme.dart';
import 'dashboard/history_page.dart';

class HistoryScreen extends StatefulWidget {
  final String initialFile;

  const HistoryScreen({
    super.key,
    required this.initialFile,
  });

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> with WindowListener {
  @override
  void initState() {
    windowManager.addListener(this);
    super.initState();
    _initWindow();
  }

  Future<void> _initWindow() async {
    await windowManager.setPreventClose(true);
  }

  @override
  void dispose() {
    windowManager.removeListener(this);
    super.dispose();
  }

  @override
  void onWindowClose() async {
    bool isPreventClose = await windowManager.isPreventClose();
    if (isPreventClose) {
      showDialog(
        context: context,
        builder: (context) => ContentDialog(
          title: const Text('Xác nhận'),
          content: const Text('Bạn có chắc chắn muốn thoát?'),
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

    return NavigationView(
      appBar: NavigationAppBar(
        automaticallyImplyLeading: false,
        title: () {
          return const DragToMoveArea(
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              child: Text('Kết quả bài thi'),
            ),
          );
        }(),
        actions: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            // Align(
            //   alignment: AlignmentDirectional.centerEnd,
            //   child: Padding(
            //     padding: const EdgeInsetsDirectional.only(end: 8.0),
            //     child: ToggleSwitch(
            //       content: const Text('Chế độ tối'),
            //       checked: FluentTheme.of(context).brightness.isDark,
            //       onChanged: (v) {
            //         if (v) {
            //           appTheme.mode = ThemeMode.dark;
            //         } else {
            //           appTheme.mode = ThemeMode.light;
            //         }
            //       },
            //     ),
            //   ),
            // ),
            const WindowButtons(),
          ],
        ),
      ),
      content: ScaffoldPage(
        padding: EdgeInsets.zero,
        content: Column(
          children: [
            // Content
            Expanded(
              child: HistoryPage(initialFile: widget.initialFile),
            ),
          ],
        ),
      ),
    );
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
