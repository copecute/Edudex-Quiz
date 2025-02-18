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
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:http/http.dart' as http;
import 'package:edudex_quiz_client/utils/cover_date_time.dart';
import 'package:edudex_quiz_client/utils/crypto.dart';
import 'package:intl/intl.dart';

class QuizScreen extends StatefulWidget {
  final int testSessionSubjectId;

  const QuizScreen({
    super.key,
    required this.testSessionSubjectId,
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

  Map<String, dynamic>? _studentData;
  List<dynamic>? _examsData;

  // Thêm biến lưu thông tin đề thi
  Map<String, dynamic>? _testPaperDetails;

  // Thêm biến lưu tổng thời gian
  int _totalSeconds = 0;

  final DateTime _startedAt = DateTime.now();

  // Thêm biến lưu log
  final List<String> _actionLogs = [];

  // Hàm thêm log
  void _addLog(String action) {
    final now = DateTime.now();
    final timestamp = DateFormat('dd-MM-yyyy-HH-mm-ss').format(now);
    _actionLogs.add('[$timestamp] $action');
  }

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
    _loadUserData();
    _loadTestPaper();
    _setupFullScreen();
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

  Future<void> _loadUserData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final studentDataStr = prefs.getString('student_data');
      final examsDataStr = prefs.getString('exams_data');
      final serverUrl = prefs.getString('server_url');

      if (studentDataStr != null && examsDataStr != null && serverUrl != null) {
        setState(() {
          _studentData = json.decode(studentDataStr);
          _examsData = json.decode(examsDataStr);

          // Cập nhật URL avatar
          if (_studentData!['avatar_url'] != null) {
            String avatarUrl = _studentData!['avatar_url'];
            if (avatarUrl.startsWith('/')) {
              avatarUrl = avatarUrl.substring(1);
            }
            _studentData!['avatar_url'] = '$serverUrl/$avatarUrl';
          }
        });
      }
    } catch (e) {
      print('Lỗi khi tải dữ liệu: $e');
    }
  }

  Future<void> _loadTestPaper() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final serverUrl = prefs.getString('server_url');
      final examsDataStr = prefs.getString('exams_data');

      if (token == null || serverUrl == null || examsDataStr == null) {
        throw Exception('Không tìm thấy thông tin cần thiết');
      }

      final examsData = json.decode(examsDataStr);
      if (examsData.isEmpty) {
        throw Exception('Không có thông tin bài thi');
      }

      final testPaperId = examsData[0]['test_paper']['id'];

      final response = await http.get(
        Uri.parse('$serverUrl/api/student/test-papers/$testPaperId/questions'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);

        // Lưu test paper details vào SharedPreferences
        await prefs.setString(
            'test_paper_details', json.encode(data['test_paper']));

        setState(() {
          _testPaperDetails = data['test_paper'];
          _questions =
              List<Map<String, dynamic>>.from(_testPaperDetails!['questions']);
          _totalSeconds = _testPaperDetails!['duration'] * 60;
          _remainingSeconds = _totalSeconds;
          _isLoading = false;
        });
      } else {
        throw Exception('Không thể tải câu hỏi');
      }
    } catch (e) {
      print('Lỗi khi tải câu hỏi: $e');
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
    _addLog('Nộp bài');
    if (_isSubmitting) return;

    setState(() {
      _isSubmitting = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final serverUrl = prefs.getString('server_url');

      if (token == null || serverUrl == null) {
        throw Exception('Không tìm thấy thông tin đăng nhập');
      }

      // Chuẩn bị dữ liệu answers cho API
      final List<Map<String, dynamic>> answers = [];
      _userAnswers.forEach((questionIndex, answerIndex) {
        final question = _questions[questionIndex];
        final answer = question['answers'][answerIndex];
        answers.add({
          'question_id': question['id'],
          'answer_id': answer['id'],
        });
      });

      // Tạo nội dung submission file theo định dạng text
      final buffer = StringBuffer();

      // Phần 1: Thông tin cá nhân
      buffer.writeln('Thông tin cá nhân:');
      buffer.writeln('Mã sinh viên: ${_studentData?['code']}');
      buffer.writeln('Họ và tên: ${_studentData?['name']}');
      buffer.writeln(
          'Chuyên ngành: ${_studentData?['majors'].firstWhere((m) => m['is_main'] == 1)['name']}');
      buffer.writeln('Số báo danh: ${_examsData![0]['exam_code']}');
      buffer.writeln();

      // Phần 2: Chi tiết đề thi
      buffer.writeln('===============================================');
      buffer.writeln('Chi tiết đề thi:');
      if (_examsData != null && _examsData!.isNotEmpty) {
        final testSession = _examsData![0]['test_session'];
        final subject = _examsData![0]['subject'];
        final room = _examsData![0]['room'];
        final shift = room['shift'];
        final testPaper = _testPaperDetails;

        buffer.writeln('Kỳ thi: ${testSession['name']}');
        buffer.writeln(
            'Thời gian kỳ thi: ${DateTimeHelper.formatDateTime(testSession['start_date'])} - ${DateTimeHelper.formatDateTime(testSession['end_date'])}');
        buffer.writeln('Môn thi: ${subject['name']} (${subject['code']})');
        buffer.writeln('Ca thi: ${shift['name']}');
        buffer.writeln(
            'Thời gian ca thi: ${DateTimeHelper.formatDateTime(shift['start_time'])} - ${DateTimeHelper.formatDateTime(shift['end_time'])}');
        buffer.writeln('Phòng thi: ${room['name']} - ${room['location']}');
        buffer.writeln();

        buffer.writeln('Tên đề thi: ${testPaper?['name']}');
        buffer.writeln('Thời gian làm bài: ${testPaper?['duration']} phút');
        buffer.writeln('Tổng số câu hỏi: ${testPaper?['total_questions']} câu');

        buffer.writeln('\nTỷ lệ độ khó:');
        final difficultyRates = testPaper?['difficulty_rates'];
        buffer.writeln('- Dễ: ${difficultyRates['easy'].toStringAsFixed(1)}%');
        buffer.writeln(
            '- Trung bình: ${difficultyRates['medium'].toStringAsFixed(1)}%');
        buffer.writeln('- Khó: ${difficultyRates['hard'].toStringAsFixed(1)}%');

        buffer.writeln('\nPhân bố theo chủ đề:');
        for (final tag in testPaper?['tags']) {
          final questionsLevel = tag['questions_by_level'];
          final rates = tag['rates'];
          buffer.writeln('${tag['name']}: ${tag['total_questions']} câu');
          buffer.writeln(
              '- Số câu theo độ khó: Dễ (${questionsLevel['easy']}), TB (${questionsLevel['medium']}), Khó (${questionsLevel['hard']})');
          buffer.writeln(
              '- Tỷ lệ: ${double.parse(rates['easy']).toStringAsFixed(1)}%/${double.parse(rates['medium']).toStringAsFixed(1)}%/${double.parse(rates['hard']).toStringAsFixed(1)}%');
        }
      }
      buffer.writeln();

      // Phần 3: Chi tiết bài làm
      buffer.writeln('===============================================');
      buffer.writeln('Chi tiết bài làm:');
      final testDetails = {
        'test_session_subject_id': _examsData![0]['test_session_subject_id'],
        'started_at': DateTimeHelper.formatDateTimeForAPI(_startedAt),
        'submitted_at': DateTimeHelper.formatDateTimeForAPI(DateTime.now()),
        'answers': answers,
      };
      final prettyJson =
          const JsonEncoder.withIndent('  ').convert(testDetails);
      buffer.writeln(prettyJson);
      buffer.writeln();

      // Phần 4: Log
      buffer.writeln('===============================================');
      buffer.writeln('Log:');
      for (final log in _actionLogs) {
        buffer.writeln(log);
      }

      // Mã hóa nội dung text
      final encryptedData =
          AppCrypto.encrypter.encrypt(buffer.toString(), iv: AppCrypto.iv);

      // Tạo request body cho API
      final requestBody = {
        'test_session_subject_id': _examsData![0]['test_session_subject_id'],
        'answers': answers,
        'submission_file': encryptedData.base64,
        'started_at': DateTimeHelper.formatDateTimeForAPI(_startedAt),
      };

      print('📤 Submit request:');
      print('URL: $serverUrl/api/student/submit-test');
      print('Headers: ${json.encode({
            'Authorization': 'copecute $token',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          })}');
      print('Body: ${json.encode(requestBody)}');

      final response = await http.post(
        Uri.parse('$serverUrl/api/student/submit-test'),
        headers: {
          'Authorization': 'copecute $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode(requestBody),
      );

      print('📥 Response status: ${response.statusCode}');
      print('📥 Response body: ${response.body}');

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
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
                submissionFile: encryptedData.base64,
                actionLogs: _actionLogs,
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
            content: Text(e.toString()),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
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
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: _studentData?['avatar_url'] != null
                          ? Image.network(
                              _studentData!['avatar_url'],
                              width: 100,
                              height: 120,
                              fit: BoxFit.cover,
                              headers: {
                                'Accept': 'image/*',
                              },
                              loadingBuilder:
                                  (context, child, loadingProgress) {
                                if (loadingProgress == null) return child;
                                return Container(
                                  width: 100,
                                  height: 120,
                                  color: Colors.grey[40],
                                  child: const Center(
                                    child: ProgressRing(),
                                  ),
                                );
                              },
                              errorBuilder: (context, error, stackTrace) {
                                // print('❌ Lỗi tải ảnh: $error');
                                // print(
                                //     '🔍 URL ảnh: ${_studentData!['avatar_url']}');
                                return Container(
                                  width: 100,
                                  height: 120,
                                  color: Colors.grey[40],
                                  child:
                                      const Icon(FluentIcons.contact, size: 48),
                                );
                              },
                            )
                          : Container(
                              width: 100,
                              height: 120,
                              color: Colors.grey[40],
                              child: const Icon(FluentIcons.contact, size: 48),
                            ),
                    ),
                    const SizedBox(width: 16),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Thông tin thí sinh',
                            style: TextStyle(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),
                        Text('Mã sinh viên: ${_studentData?['code'] ?? ''}'),
                        Text('Họ và tên: ${_studentData?['name'] ?? ''}'),
                        Text(
                            'Chuyên ngành: ${_studentData?['majors'].firstWhere((m) => m['is_main'] == 1)['name'] ?? ''}'),
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
                        if (_examsData != null && _examsData!.isNotEmpty) ...[
                          Text(
                              'Kỳ thi: ${_examsData![0]['test_session']['name']}'),
                          Text('Môn thi: ${_examsData![0]['subject']['name']}'),
                          Text(
                              'Phòng thi: ${_examsData![0]['room']['name']} - ${_examsData![0]['room']['location']}'),
                          Text(
                              'Ca thi: ${_examsData![0]['room']['shift']['name']}'),
                        ],
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
                                  Text(_questions[questionIndex]['content'],
                                      style: TextStyle(fontSize: _fontSize)),
                                  const SizedBox(height: 24),
                                  ...List.generate(
                                    _questions[questionIndex]['answers'].length,
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
                                            _selectedAnswerIndex = answerIndex;
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
                            value: (_remainingSeconds / _totalSeconds) * 100,
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
