import 'package:fluent_ui/fluent_ui.dart';
import 'package:flutter/services.dart';
import 'dart:async';
import 'result_screen.dart';
import 'package:window_manager/window_manager.dart';
import 'dart:io' show Platform;

class QuizScreen extends StatefulWidget {
  const QuizScreen({super.key});

  @override
  State<QuizScreen> createState() => _QuizScreenState();
}

class _QuizScreenState extends State<QuizScreen> with WindowListener {
  int _currentQuestionIndex = 0;
  int? _selectedAnswerIndex;
  Timer? _timer;
  int _remainingSeconds = 30 * 60; // 30 phút
  bool _isSubmitting = false;

  final List<Map<String, dynamic>> _questions = [
    {
      'question':
          'Ngôn ngữ lập trình nào được sử dụng để phát triển ứng dụng Android?',
      'answers': [
        'Swift',
        'Kotlin',
        'Objective-C',
        'Ruby',
      ],
    },
    {
      'question': 'Đâu là kiểu dữ liệu nguyên thủy trong Java?',
      'answers': [
        'String',
        'Array',
        'int',
        'Object',
      ],
    },
    {
      'question':
          'Thuật toán sắp xếp nào có độ phức tạp trung bình là O(n log n)?',
      'answers': [
        'Bubble Sort',
        'Quick Sort',
        'Selection Sort',
        'Insertion Sort',
      ],
    },
    {
      'question': 'Cấu trúc dữ liệu nào hoạt động theo nguyên tắc LIFO?',
      'answers': [
        'Queue',
        'Stack',
        'Tree',
        'Graph',
      ],
    },
    {
      'question': 'HTML là viết tắt của?',
      'answers': [
        'Hyper Text Markup Language',
        'High Tech Modern Language',
        'Hyper Transfer Markup Language',
        'High Text Machine Language',
      ],
    },
    {
      'question': 'Đâu không phải là một framework JavaScript?',
      'answers': [
        'React',
        'Angular',
        'Django',
        'Vue',
      ],
    },
    {
      'question':
          'Phương pháp nào được sử dụng để tìm kiếm trong mảng đã sắp xếp?',
      'answers': [
        'Linear Search',
        'Binary Search',
        'Hash Search',
        'Bubble Search',
      ],
    },
    {
      'question': 'Git là gì?',
      'answers': [
        'Ngôn ngữ lập trình',
        'Hệ quản trị cơ sở dữ liệu',
        'Hệ thống quản lý phiên bản',
        'Framework web',
      ],
    },
    {
      'question': 'RAM là viết tắt của?',
      'answers': [
        'Random Access Memory',
        'Read Access Memory',
        'Random Available Memory',
        'Read Available Memory',
      ],
    },
    {
      'question': 'Đâu là một trình duyệt web?',
      'answers': [
        'Windows',
        'Linux',
        'Chrome',
        'Python',
      ],
    },
    {
      'question': 'CPU là viết tắt của?',
      'answers': [
        'Central Processing Unit',
        'Central Program Utility',
        'Computer Personal Unit',
        'Control Processing Unit',
      ],
    },
    {
      'question': 'Đâu là một hệ điều hành di động?',
      'answers': [
        'Windows',
        'iOS',
        'Linux',
        'Firefox',
      ],
    },
    {
      'question': 'SQL là viết tắt của?',
      'answers': [
        'Strong Question Language',
        'Structured Query Language',
        'System Query Language',
        'Simple Question Language',
      ],
    },
    {
      'question': 'Đâu là một ngôn ngữ lập trình hướng đối tượng?',
      'answers': [
        'HTML',
        'CSS',
        'Java',
        'SQL',
      ],
    },
    {
      'question': 'Thuật toán tìm kiếm nào có độ phức tạp O(1)?',
      'answers': [
        'Linear Search',
        'Binary Search',
        'Hash Search',
        'Bubble Search',
      ],
    },
    {
      'question': 'Đâu là một protocol truyền tải web?',
      'answers': [
        'HTML',
        'CSS',
        'HTTP',
        'SQL',
      ],
    },
    {
      'question': 'Phần mềm nào được sử dụng để quản lý cơ sở dữ liệu?',
      'answers': [
        'Chrome',
        'MySQL',
        'Python',
        'Linux',
      ],
    },
    {
      'question': 'API là viết tắt của?',
      'answers': [
        'Application Programming Interface',
        'Advanced Programming Interface',
        'Application Process Integration',
        'Advanced Process Interface',
      ],
    },
    {
      'question': 'Đâu là một công cụ phát triển web?',
      'answers': [
        'Word',
        'Excel',
        'VSCode',
        'Paint',
      ],
    },
    {
      'question': 'Đâu là một cấu trúc dữ liệu phi tuyến tính?',
      'answers': [
        'Array',
        'Stack',
        'Tree',
        'Queue',
      ],
    },
  ];

  Map<int, int?> _userAnswers = {};

  final ScrollController _scrollController = ScrollController();
  final double _questionHeight =
      250; // Ước tính chiều cao trung bình của mỗi câu hỏi

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
    _setupFullScreen();
    _startTimer();
    windowManager.addListener(this);
    _initializeWindow();
  }

  void _setupFullScreen() {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: []);
    // Prevent Alt+Tab
    RawKeyboard.instance.addListener(_handleKeyEvent);
  }

  void _handleKeyEvent(RawKeyEvent event) {
    if (event.isAltPressed || event.isControlPressed) {
      // Block Alt+Tab by doing nothing
      return;
    }
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      setState(() {
        if (_remainingSeconds > 0) {
          _remainingSeconds--;
        } else {
          _submitQuiz();
        }
      });
    });
  }

  String get _formattedTime {
    int minutes = _remainingSeconds ~/ 60;
    int seconds = _remainingSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  Future<void> _submitQuiz() async {
    if (_isSubmitting) return;

    setState(() {
      _isSubmitting = true;
    });

    bool shouldSubmit = await showDialog<bool>(
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

      // Tính điểm
      int correctAnswers = 0;
      // TODO: Thay thế logic tính điểm thực tế ở đây
      _userAnswers.forEach((key, value) {
        if (value == 1) {
          // Giả sử đáp án 1 (B) là đáp án đúng
          correctAnswers++;
        }
      });

      double score = (correctAnswers * 10) / _questions.length;

      // Chuyển sang màn hình kết quả
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
      // Bỏ qua lỗi khi chạy trên web
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
    return NavigationView(
      appBar: const NavigationAppBar(
        title: Text('Bài kiểm tra'),
        automaticallyImplyLeading: false,
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
                                Text(_questions[questionIndex]['question']),
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
