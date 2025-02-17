import 'package:flutter/foundation.dart';
import 'package:flutter_acrylic/flutter_acrylic.dart';
import 'package:system_theme/system_theme.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:fluent_ui/fluent_ui.dart';

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

enum NavigationIndicators { sticky, end }

class AppTheme extends ChangeNotifier {
  static const String COLOR_KEY = 'accent_color';
  static const String THEME_MODE_KEY = 'theme_mode';
  static const String TEXT_DIRECTION_KEY = 'text_direction';

  AccentColor? _color;
  AccentColor get color => _color ?? systemAccentColor;
  set color(AccentColor color) {
    _color = color;
    _saveAccentColor(color);
    notifyListeners();
  }

  Future<void> loadSavedColor() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedColorName = prefs.getString(COLOR_KEY);

      if (savedColorName != null) {
        if (savedColorName == 'system') {
          _color = systemAccentColor;
        } else {
          final colorIndex = accentColorNames.indexOf(savedColorName) - 1;
          if (colorIndex >= 0) {
            _color = Colors.accentColors[colorIndex];
          }
        }
        notifyListeners();
      }
    } catch (e) {
      print('🎨 Lỗi khi load màu chủ đề: $e');
    }
  }

  Future<void> _saveAccentColor(AccentColor color) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      String colorName;

      if (color == systemAccentColor) {
        colorName = 'system';
      } else {
        final colorIndex = Colors.accentColors.indexOf(color);
        colorName = accentColorNames[colorIndex + 1];
      }

      await prefs.setString(COLOR_KEY, colorName);
      print('💾 Đã lưu màu chủ đề: $colorName');
    } catch (e) {
      print('🎨 Lỗi khi lưu màu chủ đề: $e');
    }
  }

  ThemeMode _mode = ThemeMode.system;
  ThemeMode get mode => _mode;
  set mode(ThemeMode mode) {
    _mode = mode;
    _saveThemeMode(mode);
    notifyListeners();
  }

  PaneDisplayMode _displayMode = PaneDisplayMode.auto;
  PaneDisplayMode get displayMode => _displayMode;
  set displayMode(PaneDisplayMode displayMode) {
    _displayMode = displayMode;
    notifyListeners();
  }

  NavigationIndicators _indicator = NavigationIndicators.sticky;
  NavigationIndicators get indicator => _indicator;
  set indicator(NavigationIndicators indicator) {
    _indicator = indicator;
    notifyListeners();
  }

  TextDirection _textDirection = TextDirection.ltr;
  TextDirection get textDirection => _textDirection;
  set textDirection(TextDirection direction) {
    _textDirection = direction;
    _saveTextDirection(direction);
    notifyListeners();
  }

  Locale? _locale;
  Locale? get locale {
    const vietnameseLocale = Locale('vi', 'VN');
    const englishLocale = Locale('en', 'US');

    final List<Locale> supportedLocales = [vietnameseLocale, englishLocale];

    if (supportedLocales.contains(_locale)) {
      return _locale;
    }

    return vietnameseLocale;
  }

  set locale(Locale? locale) {
    _locale = locale;
    notifyListeners();
  }

  Future<void> loadSettings() async {
    await loadSavedColor();
    await _loadThemeMode();
    await _loadTextDirection();
  }

  Future<void> _loadThemeMode() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedMode = prefs.getString(THEME_MODE_KEY);
      if (savedMode != null) {
        _mode = ThemeMode.values.firstWhere(
          (mode) => mode.toString() == savedMode,
          orElse: () => ThemeMode.system,
        );
        notifyListeners();
      }
    } catch (e) {
      print('🎨 Lỗi khi load chế độ giao diện: $e');
    }
  }

  Future<void> _saveThemeMode(ThemeMode mode) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(THEME_MODE_KEY, mode.toString());
      print('💾 Đã lưu chế độ giao diện: $mode');
    } catch (e) {
      print('🎨 Lỗi khi lưu chế độ giao diện: $e');
    }
  }

  Future<void> _loadTextDirection() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedDirection = prefs.getString(TEXT_DIRECTION_KEY);
      if (savedDirection != null) {
        _textDirection = TextDirection.values.firstWhere(
          (direction) => direction.toString() == savedDirection,
          orElse: () => TextDirection.ltr,
        );
        notifyListeners();
      }
    } catch (e) {
      print('🎨 Lỗi khi load hướng văn bản: $e');
    }
  }

  Future<void> _saveTextDirection(TextDirection direction) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(TEXT_DIRECTION_KEY, direction.toString());
      print('💾 Đã lưu hướng văn bản: $direction');
    } catch (e) {
      print('🎨 Lỗi khi lưu hướng văn bản: $e');
    }
  }
}

AccentColor get systemAccentColor {
  if ((defaultTargetPlatform == TargetPlatform.windows ||
          defaultTargetPlatform == TargetPlatform.android) &&
      !kIsWeb) {
    return AccentColor.swatch({
      'darkest': SystemTheme.accentColor.darkest,
      'darker': SystemTheme.accentColor.darker,
      'dark': SystemTheme.accentColor.dark,
      'normal': SystemTheme.accentColor.accent,
      'light': SystemTheme.accentColor.light,
      'lighter': SystemTheme.accentColor.lighter,
      'lightest': SystemTheme.accentColor.lightest,
    });
  }
  return Colors.blue;
}
