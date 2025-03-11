import 'package:fluent_ui/fluent_ui.dart';
import 'package:flutter/services.dart';
import 'dart:async';
import 'result_screen.dart';
import 'package:window_manager/window_manager.dart';
import 'dart:io' show Platform, File;
import 'package:provider/provider.dart';
import '../theme.dart';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:http/http.dart' as http;
import 'package:edudex_quiz_client/utils/crypto.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import 'package:edudex_quiz_client/screens/dashboard/dashboard_screen.dart';

class QuizScreen extends StatefulWidget {
  final Map<String, dynamic> examInfo;

  const QuizScreen({
    super.key,
    required this.examInfo,
  });

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

  // cỡ chữ
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
  final double _questionHeight = 300;

  Map<String, dynamic>? _studentData;
  List<dynamic>? _examsData;

  // Thêm biến lưu thông tin đề thi
  Map<String, dynamic>? _testPaperDetails;

  // Thêm biến lưu tổng thời gian
  int _totalSeconds = 0;

  final DateTime _startedAt = DateTime.now();

  // Thêm biến lưu log
  final List<String> _actionLogs = [];

  // Biến để lưu nội dung
  String submissionContent = '';

  // Thêm map để lưu key cho từng câu hỏi
  final Map<int, GlobalKey> _questionKeys = {};

  // Hàm thêm log
  void _addLog(String action) {
    final now = DateTime.now();
    final timestamp = DateFormat('dd-MM-yyyy-HH-mm-ss').format(now);
    _actionLogs.add('[$timestamp] $action');
  }

  // hàm cuộn đến câu hỏi được chọn
  void _scrollToQuestion(int index) {
    if (_scrollController.hasClients && _questionKeys.containsKey(index)) {
      // Lấy vị trí hiện tại của câu hỏi được chọn
      final RenderBox renderBox =
          _questionKeys[index]!.currentContext!.findRenderObject() as RenderBox;
      final position = renderBox.localToGlobal(Offset.zero);

      // Scroll đến vị trí của câu hỏi
      _scrollController.animateTo(
        _scrollController.offset +
            position.dy -
            250, // trừ 250 kích thước của header
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeInOut,
      );
    }
  }

  @override
  void initState() {
    super.initState();
    _loadStudentData();
    _loadTestPaper();
    _setupFullScreen();

    // Lấy thời gian từ examInfo
    _totalSeconds = widget.examInfo['exam']['duration'] * 60;
    _remainingSeconds = _totalSeconds;

    _startTimer();
    windowManager.addListener(this);
    _initializeWindow();
    RawKeyboard.instance.addListener(_handleKeyEvent);

    // Log kích thước màn hình khi khởi tạo
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final size = MediaQuery.of(context).size;
      _addLog(
          'kích thước màn hình: ${size.width.round()}x${size.height.round()}');
      _addLog('bắt đầu làm bài');
    });
  }

  Future<void> _loadStudentData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final studentDataStr = prefs.getString('student_data');

      if (studentDataStr != null) {
        setState(() {
          _studentData = json.decode(studentDataStr);
        });
        print('✅ Loaded student data: $_studentData');
      } else {
        print('❌ No student data found in SharedPreferences');
      }
    } catch (e) {
      print('❌ Error loading student data: $e');
    }
  }

  Future<void> _loadTestPaper() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final teacherIp = prefs.getString('teacher_ip');

      if (token == null || teacherIp == null) {
        throw Exception('Không tìm thấy thông tin cần thiết');
      }

      final url = 'http://$teacherIp:8689/exam-questions';

      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);

        if (data['status'] == 'success') {
          setState(() {
            // Parse questions từ response mới
            _questions =
                List<Map<String, dynamic>>.from(data['data']['questions']);
            _isLoading = false;
          });
        } else {
          throw Exception(data['message']);
        }
      } else {
        throw Exception('Không thể tải câu hỏi: ${response.statusCode}');
      }
    } catch (e) {
      print('❌ Load test paper error: $e');
      setState(() {
        _isLoading = false;
      });
      rethrow;
    }
  }

  void _setupFullScreen() {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: []);
    // Ngăn chặn Alt+Tab
    RawKeyboard.instance.addListener(_handleKeyEvent);
  }

  void _handleKeyEvent(RawKeyEvent event) {
    if (event is RawKeyDownEvent) {
      _addLog('phím: ${event.logicalKey.keyLabel}');
    }
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
                    _submitTest();
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

  Future<void> _submitTest() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final teacherIp = prefs.getString('teacher_ip');

      if (token == null || teacherIp == null) {
        throw Exception('Không tìm thấy thông tin cần thiết');
      }

      // Tạo danh sách câu trả lời
      final answers = _userAnswers.entries
          .map((e) => {
                'question_id': _questions[e.key]['id'],
                'answer_id': e.value != null
                    ? _questions[e.key]['answers'][e.value]['id']
                    : null,
              })
          .toList();

      // Tạo nội dung file log
      final buffer = StringBuffer();
      buffer.writeln('Thông tin thí sinh:');
      buffer.writeln('Mã sinh viên: ${_studentData?['student_code'] ?? ''}');
      buffer.writeln('Họ và tên: ${_studentData?['full_name'] ?? ''}');
      buffer.writeln('Số báo danh: ${_studentData?['exam_code'] ?? ''}');
      buffer.writeln();

      buffer.writeln('===============================================');
      buffer.writeln('Chi tiết đề thi:');
      buffer.writeln(
          'Môn thi: ${widget.examInfo['subject']['name']} (${widget.examInfo['subject']['code'] ?? ''}');
      buffer.writeln('Tên đề thi: ${widget.examInfo['exam']['name'] ?? ''}');
      buffer.writeln(
          'Thời gian: ${widget.examInfo['exam']['duration'] ?? ''} phút');
      buffer.writeln(
          'Số câu hỏi: ${widget.examInfo['exam']['total_questions'] ?? ''} câu');
      buffer.writeln(
          'Phòng thi: ${widget.examInfo['room']['name'] ?? ''} - ${widget.examInfo['room']['facility'] ?? ''}');
      buffer.writeln('Ca thi: ${widget.examInfo['shift']['name'] ?? ''}');
      buffer.writeln();

      buffer.writeln('===============================================');
      buffer.writeln('Chi tiết bài làm:');
      buffer.writeln('Thời gian bắt đầu: ${_startedAt.toIso8601String()}');
      buffer.writeln('Thời gian nộp bài: ${DateTime.now().toIso8601String()}');
      buffer.writeln('Câu trả lời:');
      for (final answer in answers) {
        buffer.writeln(
            '- Câu ${answer['question_id'] ?? ''} : ${answer['answer_id'] ?? ''}');
      }
      buffer.writeln();

      buffer.writeln('===============================================');
      buffer.writeln('Log:');
      for (final log in _actionLogs) {
        buffer.writeln(log);
      }

      // Mã hóa nội dung file và chuyển sang base64
      final String base64Data = AppCrypto.encryptToBase64(buffer.toString());

      // Gửi request nộp bài với format mới
      final response = await http.post(
        Uri.parse('http://$teacherIp:8689/exam-submissions'),
        headers: {
          'Authorization': 'copecute $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'answers': answers,
          'submission_file': base64Data,
          'started_at': _startedAt.toIso8601String(),
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        if (mounted) {
          Navigator.pushReplacement(
            context,
            FluentPageRoute(
              builder: (context) => ResultScreen(
                totalQuestions: data['data']['total_questions'],
                correctAnswers: data['data']['correct_answers'],
                score: data['data']['score'].toDouble(),
                questions: _questions,
                userAnswers: Map<int, int>.from(_userAnswers),
                submissionFile: base64Data,
                actionLogs: _actionLogs,
                studentInfo: {
                  'code': _studentData?['student_code'],
                  'name': _studentData?['full_name'],
                  'exam_code': _studentData?['exam_code'],
                },
                examInfo: {
                  'test_session': {
                    'name': widget.examInfo['test_session']['name'],
                  },
                  'subject': {
                    'name': widget.examInfo['subject']['name'],
                    'code': widget.examInfo['subject']['code'],
                  },
                  'room': {
                    'name': widget.examInfo['room']['name'],
                    'location': widget.examInfo['room']['facility'],
                    'shift': {
                      'name': widget.examInfo['shift']['name'],
                    },
                  },
                },
              ),
            ),
          );
        }
      } else {
        throw Exception(data['message'] ?? 'Có lỗi xảy ra khi nộp bài');
      }
    } catch (e) {
      print('❌ Submit error: $e');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Lỗi khi nộp bài: ${e.toString()}'),
                const SizedBox(height: 12),
                const Text(
                  'Bạn có thể thử nộp lại hoặc lưu kết quả vào file để nộp sau.',
                  style: TextStyle(fontStyle: FontStyle.italic),
                ),
              ],
            ),
            actions: [
              Button(
                child: const Text('Thử lại'),
                onPressed: () {
                  Navigator.pop(context);
                  _submitTest();
                },
              ),
              FilledButton(
                child: const Text('Lưu file kết quả và đóng'),
                onPressed: () async {
                  try {
                    // Tắt tạm thời always on top
                    await windowManager.setAlwaysOnTop(false);

                    final prefs = await SharedPreferences.getInstance();
                    final token = prefs.getString('token');
                    final teacherIp = prefs.getString('teacher_ip');

                    if (token == null || teacherIp == null) {
                      throw Exception('Không tìm thấy thông tin cần thiết');
                    }

                    // Tạo danh sách câu trả lời
                    final answers = _userAnswers.entries
                        .map((e) => {
                              'question_id': _questions[e.key]['id'],
                              'answer_id': e.value != null
                                  ? _questions[e.key]['answers'][e.value]['id']
                                  : null,
                            })
                        .toList();

                    // Tạo nội dung file log
                    final buffer = StringBuffer();
                    buffer.writeln('Thông tin thí sinh:');
                    buffer.writeln(
                        'Mã sinh viên: ${_studentData?['student_code'] ?? ''}');
                    buffer.writeln(
                        'Họ và tên: ${_studentData?['full_name'] ?? ''}');
                    buffer.writeln(
                        'Số báo danh: ${_studentData?['exam_code'] ?? ''}');
                    buffer.writeln();

                    buffer.writeln(
                        '===============================================');
                    buffer.writeln('Chi tiết đề thi:');
                    buffer.writeln(
                        'Môn thi: ${widget.examInfo['subject']['name']} (${widget.examInfo['subject']['code'] ?? ''}');
                    buffer.writeln(
                        'Tên đề thi: ${widget.examInfo['exam']['name'] ?? ''}');
                    buffer.writeln(
                        'Thời gian: ${widget.examInfo['exam']['duration'] ?? ''} phút');
                    buffer.writeln(
                        'Số câu hỏi: ${widget.examInfo['exam']['total_questions'] ?? ''} câu');
                    buffer.writeln(
                        'Phòng thi: ${widget.examInfo['room']['name'] ?? ''} - ${widget.examInfo['room']['facility'] ?? ''}');
                    buffer.writeln(
                        'Ca thi: ${widget.examInfo['shift']['name'] ?? ''}');
                    buffer.writeln();

                    buffer.writeln(
                        '===============================================');
                    buffer.writeln('Chi tiết bài làm:');
                    buffer.writeln(
                        'Thời gian bắt đầu: ${_startedAt.toIso8601String()}');
                    buffer.writeln(
                        'Thời gian nộp bài: ${DateTime.now().toIso8601String()}');
                    buffer.writeln('Câu trả lời:');
                    for (final answer in answers) {
                      buffer.writeln(
                          '- Câu ${answer['question_id'] ?? ''} : ${answer['answer_id'] ?? ''}');
                    }
                    buffer.writeln();

                    buffer.writeln(
                        '===============================================');
                    buffer.writeln('Log:');
                    for (final log in _actionLogs) {
                      buffer.writeln(log);
                    }
                    // Mã hóa nội dung file và chuyển thành bytes
                    final submissionFile = base64Decode(
                        AppCrypto.encryptToBase64(buffer.toString()));

                    // Lưu file
                    final now = DateTime.now();
                    final formatter = DateFormat('dd-MM-yyyy_HH-mm');
                    final fileName =
                        'ket_qua_thi_${_studentData?['student_code'] ?? 'unknown'}_${formatter.format(now)}.edudex';

                    final filePicker = await FilePicker.platform.saveFile(
                      dialogTitle: 'Lưu kết quả bài thi',
                      fileName: fileName,
                      allowedExtensions: ['edudex'],
                      type: FileType.custom,
                    );

                    if (filePicker != null) {
                      await File(filePicker).writeAsBytes(submissionFile);

                      if (!mounted) return;
                      showDialog(
                        context: context,
                        builder: (context) => ContentDialog(
                          title: const Text('Thành công'),
                          content: Column(
                            mainAxisSize: MainAxisSize.min,
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Đã lưu kết quả bài thi vào file:'),
                              const SizedBox(height: 8),
                              Text(filePicker,
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold)),
                              const SizedBox(height: 12),
                              const Text('Vui lòng nộp file này cho giám thị.'),
                            ],
                          ),
                          actions: [
                            FilledButton(
                              child: const Text('Đóng'),
                              onPressed: () {
                                Navigator.pushAndRemoveUntil(
                                  context,
                                  FluentPageRoute(
                                      builder: (context) =>
                                          const DashboardScreen()),
                                  (route) => false,
                                );
                              },
                            ),
                          ],
                        ),
                      );
                    }
                  } catch (e) {
                    showDialog(
                      context: context,
                      builder: (context) => ContentDialog(
                        title: const Text('Lỗi'),
                        content: Text('Không thể lưu file: ${e.toString()}'),
                        actions: [
                          Button(
                            child: const Text('Đóng'),
                            onPressed: () => Navigator.pop(context),
                          ),
                        ],
                      ),
                    );
                  } finally {
                    // Bật lại chế độ "always on top"
                    await windowManager.setAlwaysOnTop(true);
                  }
                },
              ),
            ],
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
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

  // Thêm hàm xử lý click chuột
  void _handleMouseClick(TapDownDetails details) {
    _addLog(
        'click chuột tại vị trí: (${details.globalPosition.dx.round()}, ${details.globalPosition.dy.round()})');
  }

  // Cập nhật hàm chọn đáp án
  void _selectAnswer(int questionIndex, int answerIndex) {
    setState(() {
      _userAnswers[questionIndex] = answerIndex;
    });
    _addLog('chọn đáp án ${answerIndex + 1} cho câu ${questionIndex + 1}');
  }

  // Cập nhật hàm thay đổi font size
  void _changeFontSize(double newSize) {
    setState(() {
      _fontSize = newSize;
    });
    _addLog('thay đổi cỡ chữ: $_fontSize');
  }

  // Cập nhật hàm chuyển đổi theme
  void _toggleTheme(bool isDark) {
    final appTheme = context.read<AppTheme>();
    appTheme.mode = isDark ? ThemeMode.dark : ThemeMode.light;
    _addLog('chuyển sang ${isDark ? "chế độ tối" : "chế độ sáng"}');
  }

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();

    if (_isLoading) {
      return const Center(child: ProgressRing());
    }

    return GestureDetector(
      onTapDown: _handleMouseClick,
      child: NavigationView(
        appBar: NavigationAppBar(
          automaticallyImplyLeading: false,
          leading: Row(
            children: const [
              SizedBox(width: 8),
              Icon(FluentIcons.defender_app),
              SizedBox(width: 4),
              Text('Được áp dụng công nghệ chống gian lận'),
            ],
          ),
          actions: Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              // Nút điều chỉnh cỡ chữ
              Row(
                children: [
                  IconButton(
                    icon: const Icon(FluentIcons.font_decrease),
                    onPressed: () {
                      if (_fontSize > 12) {
                        _changeFontSize(_fontSize - 2);
                      }
                    },
                  ),
                  IconButton(
                    icon: const Icon(FluentIcons.font_size),
                    onPressed: () {
                      _changeFontSize(16.0);
                    },
                  ),
                  IconButton(
                    icon: const Icon(FluentIcons.font_increase),
                    onPressed: () {
                      if (_fontSize < 24) {
                        _changeFontSize(_fontSize + 2);
                      }
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
                  onChanged: _toggleTheme,
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
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Thông tin thí sinh',
                            style: TextStyle(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),
                        Text(
                            'Mã sinh viên: ${_studentData?['student_code'] ?? ''}'),
                        Text('Họ và tên: ${_studentData?['full_name'] ?? ''}'),
                        Text(
                            'Số báo danh: ${_studentData?['exam_code'] ?? ''}'),
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
                        Text(
                            'Môn thi: ${widget.examInfo['subject']['name']} (${widget.examInfo['subject']['code']})'),
                        Text('Tên đề thi: ${widget.examInfo['exam']['name']}'),
                        Text(
                            'Thời gian: ${widget.examInfo['exam']['duration']} phút'),
                        Text(
                            'Số câu hỏi: ${widget.examInfo['exam']['total_questions']} câu'),
                        Text(
                            'Phòng thi: ${widget.examInfo['room']['name']} - ${widget.examInfo['room']['facility']}'),
                        Text('Ca thi: ${widget.examInfo['shift']['name']}'),
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
                          border: Border(
                              right: BorderSide(color: Colors.grey[30]!)),
                        ),
                        child: SingleChildScrollView(
                          controller: _scrollController,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: List.generate(
                              _questions.length,
                              (questionIndex) {
                                // Tạo key cho mỗi câu hỏi nếu chưa có
                                _questionKeys[questionIndex] =
                                    _questionKeys[questionIndex] ?? GlobalKey();

                                return Container(
                                  key: _questionKeys[
                                      questionIndex], // Gán key cho container của câu hỏi
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'CÂU ${questionIndex + 1}:',
                                        style: const TextStyle(
                                          color: Color(0xFFD83B01),
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 16),
                                      Text(_questions[questionIndex]['content'],
                                          style:
                                              TextStyle(fontSize: _fontSize)),
                                      const SizedBox(height: 24),
                                      ...List.generate(
                                        _questions[questionIndex]['answers']
                                            .length,
                                        (answerIndex) => Padding(
                                          padding:
                                              const EdgeInsets.only(bottom: 16),
                                          child: RadioButton(
                                            checked: questionIndex ==
                                                        _currentQuestionIndex &&
                                                    _selectedAnswerIndex ==
                                                        answerIndex ||
                                                _userAnswers[questionIndex] ==
                                                    answerIndex,
                                            onChanged: (value) {
                                              setState(() {
                                                _currentQuestionIndex =
                                                    questionIndex;
                                                _selectedAnswerIndex =
                                                    answerIndex;
                                                _userAnswers[questionIndex] =
                                                    answerIndex;
                                              });
                                            },
                                            content: Text(
                                              '${String.fromCharCode(65 + answerIndex)}. ${_questions[questionIndex]['answers'][answerIndex]['content']}',
                                              style: TextStyle(
                                                  fontSize: _fontSize - 2),
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              },
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
                            value: _totalSeconds > 0
                                ? (_remainingSeconds / _totalSeconds * 100)
                                    .clamp(0, 100)
                                : 0,
                            backgroundColor: Colors.grey[30],
                            activeColor:
                                _remainingSeconds < (_totalSeconds * 0.25)
                                    ? Colors.red
                                    : (_remainingSeconds < (_totalSeconds * 0.5)
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
                                final isSelected =
                                    _currentQuestionIndex == index;

                                return Button(
                                  onPressed: () {
                                    setState(() {
                                      _currentQuestionIndex = index;
                                      _selectedAnswerIndex =
                                          _userAnswers[index];
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
                                            style:
                                                const TextStyle(fontSize: 10),
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
                            onPressed: _isSubmitting ? null : _submitTest,
                            child: _isSubmitting
                                ? const Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      SizedBox(
                                        width: 16,
                                        height: 16,
                                        child: ProgressRing(),
                                      ),
                                      SizedBox(width: 8),
                                      Text('Đang nộp bài...'),
                                    ],
                                  )
                                : const Text('Nộp bài'),
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Bài thi kết thúc khi hết thời gian hoặc khi thí sinh nhấn vào nút "Nộp bài"',
                            style: TextStyle(
                                color: Color(0xFFD83B01), fontSize: 12),
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
      ),
    );
  }
}
