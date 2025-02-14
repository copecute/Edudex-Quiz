// ignore_for_file: constant_identifier_names

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:fluent_ui/fluent_ui.dart';
import 'package:flutter_acrylic/flutter_acrylic.dart';
import 'package:provider/provider.dart';
import '../screens/splash_screen.dart';

import '../theme.dart';
import '../widgets/page.dart';

const List<String> accentColorNames = [
  'System',
  'Yellow',
  'Orange',
  'Red',
  'Magenta',
  'Purple',
  'Blue',
  'Teal',
  'Green',
];

bool get kIsWindowEffectsSupported {
  return !kIsWeb &&
      [
        TargetPlatform.windows,
        TargetPlatform.linux,
        TargetPlatform.macOS,
      ].contains(defaultTargetPlatform);
}

const _LinuxWindowEffects = [
  WindowEffect.disabled,
  WindowEffect.transparent,
];

const _WindowsWindowEffects = [
  WindowEffect.disabled,
  WindowEffect.solid,
  WindowEffect.transparent,
  WindowEffect.aero,
  WindowEffect.acrylic,
  WindowEffect.mica,
  WindowEffect.tabbed,
];

const _MacosWindowEffects = [
  WindowEffect.disabled,
  WindowEffect.titlebar,
  WindowEffect.selection,
  WindowEffect.menu,
  WindowEffect.popover,
  WindowEffect.sidebar,
  WindowEffect.headerView,
  WindowEffect.sheet,
  WindowEffect.windowBackground,
  WindowEffect.hudWindow,
  WindowEffect.fullScreenUI,
  WindowEffect.toolTip,
  WindowEffect.contentBackground,
  WindowEffect.underWindowBackground,
  WindowEffect.underPageBackground,
];

List<WindowEffect> get currentWindowEffects {
  if (kIsWeb) return [];

  if (defaultTargetPlatform == TargetPlatform.windows) {
    return _WindowsWindowEffects;
  } else if (defaultTargetPlatform == TargetPlatform.linux) {
    return _LinuxWindowEffects;
  } else if (defaultTargetPlatform == TargetPlatform.macOS) {
    return _MacosWindowEffects;
  }

  return [];
}

class Settings extends StatefulWidget {
  const Settings({super.key});

  @override
  State<Settings> createState() => _SettingsState();
}

class _SettingsState extends State<Settings> with PageMixin {
  Future<void> _disconnectServer() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('server_url');
    await prefs.remove('user_token');
    await prefs.remove('user_id');
    await prefs.remove('username');
    await prefs.remove('email');
    await prefs.remove('user_role');
    print('🔌 Đã ngắt kết nối và xóa thông tin server/người dùng');

    if (!mounted) return;

    Navigator.pushAndRemoveUntil(
      context,
      FluentPageRoute(builder: (context) => const SplashScreen()),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    assert(debugCheckHasMediaQuery(context));
    final appTheme = context.watch<AppTheme>();
    const spacer = SizedBox(height: 10.0);
    const biggerSpacer = SizedBox(height: 40.0);

    const supportedLocales = FluentLocalizations.supportedLocales;
    final currentLocale =
        appTheme.locale ?? Localizations.maybeLocaleOf(context);

    return ScaffoldPage.scrollable(
      header: const PageHeader(title: Text('Cài đặt')),
      children: [
        Text('Chế độ giao diện',
            style: FluentTheme.of(context).typography.subtitle),
        spacer,
        ...List.generate(ThemeMode.values.length, (index) {
          final mode = ThemeMode.values[index];
          return Padding(
            padding: const EdgeInsetsDirectional.only(bottom: 8.0),
            child: RadioButton(
              checked: appTheme.mode == mode,
              onChanged: (value) {
                if (value) {
                  appTheme.mode = mode;
                  if (kIsWindowEffectsSupported) {
                    appTheme.setEffect(appTheme.windowEffect, context);
                  }
                }
              },
              content: Text(
                '$mode'
                    .replaceAll('ThemeMode.', '')
                    .replaceAll('system', 'Hệ thống')
                    .replaceAll('light', 'Sáng')
                    .replaceAll('dark', 'Tối'),
              ),
            ),
          );
        }),
        biggerSpacer,
        Text('Kiểu hiển thị thanh điều hướng',
            style: FluentTheme.of(context).typography.subtitle),
        spacer,
        ...List.generate(PaneDisplayMode.values.length, (index) {
          final mode = PaneDisplayMode.values[index];
          return Padding(
            padding: const EdgeInsetsDirectional.only(bottom: 8.0),
            child: RadioButton(
              checked: appTheme.displayMode == mode,
              onChanged: (value) {
                if (value) appTheme.displayMode = mode;
              },
              content: Text(
                mode
                    .toString()
                    .replaceAll('PaneDisplayMode.', '')
                    .replaceAll('top', 'Trên cùng')
                    .replaceAll('open', 'Mở rộng')
                    .replaceAll('compact', 'Thu gọn')
                    .replaceAll('minimal', 'Tối giản'),
              ),
            ),
          );
        }),
        biggerSpacer,
        Text('Chỉ báo điều hướng',
            style: FluentTheme.of(context).typography.subtitle),
        spacer,
        ...List.generate(NavigationIndicators.values.length, (index) {
          final mode = NavigationIndicators.values[index];
          return Padding(
            padding: const EdgeInsetsDirectional.only(bottom: 8.0),
            child: RadioButton(
              checked: appTheme.indicator == mode,
              onChanged: (value) {
                if (value) appTheme.indicator = mode;
              },
              content: Text(
                mode
                    .toString()
                    .replaceAll('NavigationIndicators.', '')
                    .replaceAll('sticky', 'Cố định')
                    .replaceAll('end', 'Cuối')
                    .replaceAll('start', 'Đầu'),
              ),
            ),
          );
        }),
        biggerSpacer,
        Text('Màu chủ đề', style: FluentTheme.of(context).typography.subtitle),
        spacer,
        Wrap(children: [
          Tooltip(
            message: 'Hệ thống',
            child: _buildColorBlock(appTheme, systemAccentColor),
          ),
          ...List.generate(Colors.accentColors.length, (index) {
            final color = Colors.accentColors[index];
            return Tooltip(
              message: accentColorNames[index + 1]
                  .replaceAll('Yellow', 'Vàng')
                  .replaceAll('Orange', 'Cam')
                  .replaceAll('Red', 'Đỏ')
                  .replaceAll('Magenta', 'Hồng')
                  .replaceAll('Purple', 'Tím')
                  .replaceAll('Blue', 'Xanh dương')
                  .replaceAll('Teal', 'Xanh ngọc')
                  .replaceAll('Green', 'Xanh lá'),
              child: _buildColorBlock(appTheme, color),
            );
          }),
        ]),
        if (kIsWindowEffectsSupported) ...[
          biggerSpacer,
          Text('Hiệu ứng cửa sổ',
              style: FluentTheme.of(context).typography.subtitle),
          description(
            content: Text(
              'Đang chạy trên ${defaultTargetPlatform.toString().replaceAll('TargetPlatform.', '')}',
            ),
          ),
          spacer,
          ...List.generate(currentWindowEffects.length, (index) {
            final mode = currentWindowEffects[index];
            return Padding(
              padding: const EdgeInsetsDirectional.only(bottom: 8.0),
              child: RadioButton(
                checked: appTheme.windowEffect == mode,
                onChanged: (value) {
                  if (value) {
                    appTheme.windowEffect = mode;
                    appTheme.setEffect(mode, context);
                  }
                },
                content: Text(
                  mode
                      .toString()
                      .replaceAll('WindowEffect.', '')
                      .replaceAll('disabled', 'Tắt')
                      .replaceAll('solid', 'Đặc')
                      .replaceAll('transparent', 'Trong suốt')
                      .replaceAll('aero', 'Aero')
                      .replaceAll('acrylic', 'Acrylic')
                      .replaceAll('mica', 'Mica')
                      .replaceAll('tabbed', 'Tab'),
                ),
              ),
            );
          }),
        ],
        biggerSpacer,
        Text('Hướng văn bản',
            style: FluentTheme.of(context).typography.subtitle),
        spacer,
        ...List.generate(TextDirection.values.length, (index) {
          final direction = TextDirection.values[index];
          return Padding(
            padding: const EdgeInsetsDirectional.only(bottom: 8.0),
            child: RadioButton(
              checked: appTheme.textDirection == direction,
              onChanged: (value) {
                if (value) appTheme.textDirection = direction;
              },
              content: Text(
                direction == TextDirection.ltr
                    ? 'Trái sang phải'
                    : 'Phải sang trái',
              ),
            ),
          );
        }).reversed,
        biggerSpacer,
        Text('Ngôn ngữ', style: FluentTheme.of(context).typography.subtitle),
        description(
          content: const Text(
            'Ngôn ngữ được sử dụng cho các widget như TimePicker và DatePicker.',
          ),
        ),
        spacer,
        Wrap(
          spacing: 15.0,
          runSpacing: 10.0,
          children: List.generate(
            supportedLocales.length,
            (index) {
              final locale = supportedLocales[index];
              return Padding(
                padding: const EdgeInsetsDirectional.only(bottom: 8.0),
                child: RadioButton(
                  checked: currentLocale == locale,
                  onChanged: (value) {
                    if (value) appTheme.locale = locale;
                  },
                  content: Text('$locale'),
                ),
              );
            },
          ),
        ),
        biggerSpacer,
        Text('Kết nối máy chủ',
            style: FluentTheme.of(context).typography.subtitle),
        spacer,
        FilledButton(
          style: ButtonStyle(
            backgroundColor: ButtonState.resolveWith((states) {
              if (states.isPressed) {
                return Colors.errorPrimaryColor;
              }
              return Colors.errorSecondaryColor;
            }),
          ),
          onPressed: () {
            showDialog(
              context: context,
              builder: (context) => ContentDialog(
                title: const Text('Xác nhận ngắt kết nối'),
                content: const Text(
                    'Bạn có chắc chắn muốn ngắt kết nối khỏi máy chủ? Ứng dụng sẽ quay về màn hình kết nối.'),
                actions: [
                  FilledButton(
                    child: const Text('Có'),
                    onPressed: () {
                      Navigator.pop(context);
                      _disconnectServer();
                    },
                  ),
                  Button(
                    child: const Text('Không'),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            );
          },
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(FluentIcons.plug_disconnected),
              SizedBox(width: 8),
              Text('Ngắt kết nối máy chủ'),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildColorBlock(AppTheme appTheme, AccentColor color) {
    return Padding(
      padding: const EdgeInsets.all(2.0),
      child: Button(
        onPressed: () {
          appTheme.color = color;
        },
        style: ButtonStyle(
          padding: const WidgetStatePropertyAll(EdgeInsets.zero),
          backgroundColor: WidgetStateProperty.resolveWith((states) {
            if (states.isPressed) {
              return color.light;
            } else if (states.isHovered) {
              return color.lighter;
            }
            return color;
          }),
        ),
        child: Container(
          height: 40,
          width: 40,
          alignment: AlignmentDirectional.center,
          child: appTheme.color == color
              ? Icon(
                  FluentIcons.check_mark,
                  color: color.basedOnLuminance(),
                  size: 22.0,
                )
              : null,
        ),
      ),
    );
  }
}
