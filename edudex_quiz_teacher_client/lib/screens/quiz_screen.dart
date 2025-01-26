import 'package:fluent_ui/fluent_ui.dart';
import 'package:flutter/services.dart';
import 'dart:async';
import 'result_screen.dart';
import 'package:window_manager/window_manager.dart';
import 'dart:io' show Platform;
import 'package:provider/provider.dart';
import '../theme.dart';
import 'dart:convert';
import 'package:flutter/foundation.dart';

class QuizScreen extends StatefulWidget {
  const QuizScreen({super.key});

  @override
  State<QuizScreen> createState() => _QuizScreenState();
}

class _QuizScreenState extends State<QuizScreen> with WindowListener {
  // biến lưu câu hỏi hiện tại
  int _currentQuestionIndex = 0;

  // đáp án được chọn cho câu hỏi hiện tại
  int? _selectedAnswerIndex;

  // biến đếm thời gian làm bài
  Timer? _timer;

  int _remainingSeconds = 1 * 60; // 1 phút

  // biến trạng thái đang nộp bài
  bool _isSubmitting = false;

  // cỡ chữ hiện tại
  double _fontSize = 16.0;

  // biến lưu danh sách câu hỏi từ file json
  List<Map<String, dynamic>> _questions = [];

  // trạng thái đang tải câu hỏi
  bool _isLoading = true;

  // biến lưu đáp án của người dùng (số thứ tự câu hỏi -> đáp án đã chọn)
  Map<int, int?> _userAnswers = {};

  // biến controller cuộn màn hình
  final ScrollController _scrollController = ScrollController();

  // biến chiều cao ước tính cho mỗi câu hỏi
  final double _questionHeight = 250;

  // hàm cuộn đến câu hỏi được chọn
  void _scrollToQuestion(int index) {
    final double offset = index * _questionHeight;
    _scrollController.animateTo(
      offset,
      duration: const Duration(milliseconds: 500),
      curve: Curves.easeInOut,
    );
  }

  @override
  void initState() {
    super.initState();
    // tải câu hỏi khi khởi tạo
    _loadQuestions();
    // toàn màn hình
    _setupFullScreen();
    // bắt đầu đếm thời gian
    _startTimer();
    // thêm listener cho window
    windowManager.addListener(this);
    // khởi tạo cửa sổ
    _initializeWindow();
    // Thêm listener cho keyboard
    RawKeyboard.instance.addListener(_handleKeyEvent);
  }

  Future<void> _loadQuestions() async {
    try {
      // đọc file JSON từ assets
      final String response = await DefaultAssetBundle.of(context)
          .loadString('assets/questions.json');
      final data = await json.decode(response);

      setState(() {
        _questions = List<Map<String, dynamic>>.from(data['questions']);
        _isLoading = false;
      });
    } catch (e) {
      debugPrint('Lỗi khi tải câu hỏi: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _setupFullScreen() {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: []);
    // Ngăn chặn Alt+Tab
    RawKeyboard.instance.addListener(_handleKeyEvent);
  }

  void _handleKeyEvent(RawKeyEvent event) {
    // Chặn các phím Windows/Super
    if (event.isMetaPressed) {
      return;
    }

    // Chặn Alt+Tab, Windows+Tab
    if (event.isAltPressed || event.isMetaPressed) {
      if (event.logicalKey == LogicalKeyboardKey.tab) {
        return;
      }
    }

    // Chặn Windows+D (Show desktop)
    if (event.isMetaPressed && event.logicalKey == LogicalKeyboardKey.keyD) {
      return;
    }

    // Chặn Ctrl+Alt+Delete
    if (event.isControlPressed && event.isAltPressed) {
      if (event.logicalKey == LogicalKeyboardKey.delete) {
        return;
      }
    }

    // Chặn Alt+F4
    if (event.isAltPressed && event.logicalKey == LogicalKeyboardKey.f4) {
      return;
    }

    // Chặn Ctrl+W
    if (event.isControlPressed && event.logicalKey == LogicalKeyboardKey.keyW) {
      return;
    }

    // Chặn các phím chức năng F1-F12
    if (event.logicalKey.keyLabel.startsWith('F') &&
        event.logicalKey.keyLabel.length <= 3) {
      return;
    }

    // Chặn Ctrl+Shift+Esc (Task Manager)
    if (event.isControlPressed &&
        event.isShiftPressed &&
        event.logicalKey == LogicalKeyboardKey.escape) {
      return;
    }

    // Chặn Alt+Esc
    if (event.isAltPressed && event.logicalKey == LogicalKeyboardKey.escape) {
      return;
    }
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      setState(() {
        if (_remainingSeconds > 0) {
          _remainingSeconds--;
        } else {
          _timer?.cancel();
          // hiện modal hết giờ
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => ContentDialog(
              title: const Text('Hết giờ làm bài'),
              content: const Text(
                  'Đã hết thời gian làm bài, hệ thống sẽ tự động nộp bài.'),
              actions: [
                FilledButton(
                  child: const Text('OK'),
                  onPressed: () {
                    Navigator.pop(context);
                    _submitQuiz(isTimeUp: true);
                  },
                ),
              ],
            ),
          );
        }
      });
    });
  }

  String get _formattedTime {
    int minutes = _remainingSeconds ~/ 60;
    int seconds = _remainingSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  void _submitQuiz({bool isTimeUp = false}) async {
    if (_isSubmitting) return;

    setState(() {
      _isSubmitting = true;
    });

    // nếu không phải hết giờ thì hiện dialog xác nhận
    bool shouldSubmit = isTimeUp
        ? true
        : await showDialog<bool>(
              context: context,
              barrierDismissible: false,
              builder: (context) => ContentDialog(
                title: const Text('Nộp bài'),
                content: const Text('Bạn có chắc chắn muốn nộp bài?'),
                actions: [
                  Button(
                    child: const Text('Không'),
                    onPressed: () => Navigator.pop(context, false),
                  ),
                  FilledButton(
                    child: const Text('Có'),
                    onPressed: () => Navigator.pop(context, true),
                  ),
                ],
              ),
            ) ??
            false;

    if (shouldSubmit) {
      _timer?.cancel();
      SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual,
          overlays: SystemUiOverlay.values);
      RawKeyboard.instance.removeListener(_handleKeyEvent);

      // Tắt full screen và always on top
      try {
        if (const bool.fromEnvironment('dart.library.io')) {
          await windowManager.setFullScreen(false);
          await windowManager.setAlwaysOnTop(false);
        }
      } catch (e) {
        // Bỏ qua lỗi khi chạy trên web
      }

      // Tính điểm
      int correctAnswers = 0;
      _userAnswers.forEach((key, value) {
        if (value == 1) {
          correctAnswers++;
        }
      });

      double score = (correctAnswers * 10) / _questions.length;

      if (mounted) {
        Navigator.pushReplacement(
          context,
          FluentPageRoute(
            builder: (context) => ResultScreen(
              totalQuestions: _questions.length,
              correctAnswers: correctAnswers,
              score: score,
            ),
          ),
        );
      }
    } else {
      setState(() {
        _isSubmitting = false;
      });
    }
  }

  Future<void> _initializeWindow() async {
    try {
      if (const bool.fromEnvironment('dart.library.io')) {
        await windowManager.ensureInitialized();
        await windowManager.setFullScreen(true);
        await windowManager.setAlwaysOnTop(true);
        await windowManager.focus();
      }
    } catch (e) {
      // Bỏ qua lỗi khi debug web
    }
  }

  @override
  void dispose() {
    try {
      if (const bool.fromEnvironment('dart.library.io')) {
        windowManager.removeListener(this);
        windowManager.setFullScreen(false);
        windowManager.setAlwaysOnTop(false);
      }
    } catch (e) {
      // Bỏ qua lỗi khi chạy trên web
    }
    _scrollController.dispose();
    _timer?.cancel();
    RawKeyboard.instance.removeListener(_handleKeyEvent);
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual,
        overlays: SystemUiOverlay.values);
    super.dispose();
  }

  @override
  void onWindowClose() async {
    bool isPreventClose = await windowManager.isPreventClose();
    if (isPreventClose) {
      showDialog(
        context: context,
        builder: (_) {
          return ContentDialog(
            title: const Text('Xác nhận'),
            content: const Text('Bạn có chắc chắn muốn thoát khỏi bài thi?'),
            actions: [
              Button(
                child: const Text('Không'),
                onPressed: () {
                  Navigator.of(context).pop();
                },
              ),
              FilledButton(
                child: const Text('Có'),
                onPressed: () {
                  Navigator.of(context).pop();
                  windowManager.destroy();
                },
              ),
            ],
          );
        },
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();

    if (_isLoading) {
      return const Center(child: ProgressRing());
    }

    return NavigationView(
      appBar: NavigationAppBar(
        automaticallyImplyLeading: false,
        // title: const Text('Làm bài'),
        actions: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            // Nút điều chỉnh cỡ chữ
            Row(
              children: [
                IconButton(
                  icon: const Icon(FluentIcons.font_decrease),
                  onPressed: () {
                    setState(() {
                      if (_fontSize > 12) {
                        _fontSize -= 2;
                      }
                    });
                  },
                ),
                IconButton(
                  icon: const Icon(FluentIcons.font_size),
                  onPressed: () {
                    setState(() {
                      _fontSize = 16.0; // Reset về mặc định
                    });
                  },
                ),
                IconButton(
                  icon: const Icon(FluentIcons.font_increase),
                  onPressed: () {
                    setState(() {
                      if (_fontSize < 24) {
                        _fontSize += 2;
                      }
                    });
                  },
                ),
                const SizedBox(width: 16),
              ],
            ),
            // Nút chuyển đổi theme
            Padding(
              padding: const EdgeInsets.only(right: 8.0),
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
          ],
        ),
      ),
      content: ScaffoldPage(
        padding: EdgeInsets.zero,
        content: Column(
          children: [
            // Header với thông tin thí sinh và bài thi
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border(bottom: BorderSide(color: Colors.grey[30]!)),
              ),
              child: Row(
                children: [
                  // Ảnh và thông tin thí sinh
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: Image.network(
                      'URL_ẢNH_THÍ_SINH', // Thay bằng URL ảnh thực tế
                      width: 100,
                      height: 120,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => Container(
                        width: 100,
                        height: 120,
                        color: Colors.grey[40],
                        child: const Icon(FluentIcons.contact, size: 48),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Thông tin thí sinh',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      Text('Mã sinh viên: 2209620321'),
                      Text('Họ và tên: Đàm Minh Giang'),
                      Text('Lớp: 2622CNT04'),
                    ],
                  ),
                  const Spacer(),
                  // Thông tin bài thi
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Thông tin bài thi',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      Text('Tên bài thi: Bài thi kết thúc môn'),
                      Text('Môn học: Cấu trúc dữ liệu & Giải thuật'),
                      Text('Từ 30/06/2024 2h30 đến 3h30 cùng ngày'),
                    ],
                  ),
                ],
              ),
            ),
            // Nội dung bài thi
            Expanded(
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Phần câu hỏi
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        border:
                            Border(right: BorderSide(color: Colors.grey[30]!)),
                      ),
                      child: SingleChildScrollView(
                        controller: _scrollController,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: List.generate(
                            _questions.length,
                            (questionIndex) => Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'CÂU ${questionIndex + 1}:',
                                  style: const TextStyle(
                                    color: Color(0xFFD83B01),
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                Text(_questions[questionIndex]['question'],
                                    style: TextStyle(fontSize: _fontSize)),
                                const SizedBox(height: 24),
                                ...List.generate(
                                  _questions[questionIndex]['answers'].length,
                                  (answerIndex) => Padding(
                                    padding: const EdgeInsets.only(bottom: 16),
                                    child: RadioButton(
                                      checked: questionIndex ==
                                                  _currentQuestionIndex &&
                                              _selectedAnswerIndex ==
                                                  answerIndex ||
                                          _userAnswers[questionIndex] ==
                                              answerIndex,
                                      onChanged: (value) {
                                        setState(() {
                                          _currentQuestionIndex = questionIndex;
                                          _selectedAnswerIndex = answerIndex;
                                          _userAnswers[questionIndex] =
                                              answerIndex;
                                        });
                                      },
                                      content: Text(
                                        '${String.fromCharCode(65 + answerIndex)}. ${_questions[questionIndex]['answers'][answerIndex]}',
                                        style:
                                            TextStyle(fontSize: _fontSize - 2),
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(
                                    height: 32), // Khoảng cách giữa các câu
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Phần bên phải
                  Container(
                    width: 300,
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          'Thời gian còn lại: $_formattedTime',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        // Thêm progress bar thời gian
                        ProgressBar(
                          value: (_remainingSeconds / (1 * 60)) * 100,
                          backgroundColor: Colors.grey[30],
                          activeColor: _remainingSeconds < 30
                              ? Colors.red
                              : (_remainingSeconds < 60
                                  ? Colors.orange
                                  : Colors.blue),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Bảng đáp án',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Expanded(
                          child: GridView.builder(
                            gridDelegate:
                                const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 4,
                              childAspectRatio: 1,
                              crossAxisSpacing: 8,
                              mainAxisSpacing: 8,
                            ),
                            itemCount: _questions.length,
                            itemBuilder: (context, index) {
                              final isAnswered =
                                  _userAnswers.containsKey(index);
                              final isSelected = _currentQuestionIndex == index;

                              return Button(
                                onPressed: () {
                                  setState(() {
                                    _currentQuestionIndex = index;
                                    _selectedAnswerIndex = _userAnswers[index];
                                  });
                                  _scrollToQuestion(index);
                                },
                                style: ButtonStyle(
                                  padding: ButtonState.all(EdgeInsets.zero),
                                  backgroundColor: ButtonState.all(
                                    isAnswered
                                        ? Colors.green.lightest
                                        : (isSelected
                                            ? Colors.blue.lightest
                                            : null),
                                  ),
                                ),
                                child: Stack(
                                  children: [
                                    Center(
                                      child: Text('${index + 1}'),
                                    ),
                                    if (isAnswered)
                                      Positioned(
                                        right: 4,
                                        bottom: 4,
                                        child: Text(
                                          String.fromCharCode(
                                              65 + _userAnswers[index]!),
                                          style: const TextStyle(fontSize: 10),
                                        ),
                                      ),
                                  ],
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(height: 16),
                        FilledButton(
                          onPressed: _submitQuiz,
                          child: const Padding(
                            padding: EdgeInsets.all(8.0),
                            child: Text('Nộp bài'),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Bài thi kết thúc khi hết thời gian hoặc khi thí sinh nhấn vào nút "Nộp bài"',
                          style:
                              TextStyle(color: Color(0xFFD83B01), fontSize: 12),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
