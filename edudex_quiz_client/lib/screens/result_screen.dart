import 'dart:convert';
import 'dart:io';
import 'package:fluent_ui/fluent_ui.dart';
import 'package:window_manager/window_manager.dart';
import 'package:provider/provider.dart';
import '../theme.dart';
import 'package:edudex_quiz_client/screens/dashboard/dashboard_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:intl/intl.dart';
import '../utils/crypto.dart';
import 'dart:developer';
import 'package:process_run/process_run.dart';
import 'package:file_picker/file_picker.dart';

class ResultScreen extends StatefulWidget {
  final int totalQuestions;
  final int correctAnswers;
  final double score;
  final List<dynamic> questions;
  final Map<int, int> userAnswers;
  final String submissionFile;
  final List<String> actionLogs;

  const ResultScreen({
    super.key,
    required this.totalQuestions,
    required this.correctAnswers,
    required this.score,
    required this.questions,
    required this.userAnswers,
    required this.submissionFile,
    required this.actionLogs,
  });

  @override
  State<ResultScreen> createState() => _ResultScreenState();
}

class _ResultScreenState extends State<ResultScreen> {
  Future<void> _saveResult() async {
    try {
      final now = DateTime.now();
      final fileName = 'result_${now.millisecondsSinceEpoch}.edudex';

      final result = await FilePicker.platform.saveFile(
        dialogTitle: 'Lưu kết quả bài thi',
        fileName: fileName,
        allowedExtensions: ['edudex'],
        type: FileType.custom,
      );

      if (result != null) {
        final file = File(result);

        // Lấy dữ liệu từ SharedPreferences
        final prefs = await SharedPreferences.getInstance();
        final studentDataStr = prefs.getString('student_data');
        final examsDataStr = prefs.getString('exams_data');
        final testPaperStr = prefs.getString('test_paper_details');

        // Kiểm tra dữ liệu cần thiết
        final List<String> missingData = [];
        if (studentDataStr == null) missingData.add('Thông tin sinh viên');
        if (examsDataStr == null) missingData.add('Thông tin kỳ thi');
        if (testPaperStr == null) missingData.add('Thông tin đề thi');

        if (missingData.isNotEmpty) {
          // Tạo nội dung thông báo kiểm tra khi có lỗi
          final buffer = StringBuffer();
          buffer.writeln('❌ Thiếu dữ liệu:');
          for (final item in missingData) {
            buffer.writeln('- $item');
          }

          if (mounted) {
            await showDialog(
              context: context,
              builder: (context) => ContentDialog(
                title: const Text('Lỗi dữ liệu'),
                content: SingleChildScrollView(
                  child: Text(buffer.toString()),
                ),
                actions: [
                  Button(
                    child: const Text('Đóng'),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            );
          }
          throw Exception('Không tìm thấy dữ liệu cần thiết');
        }

        // Đã kiểm tra null ở trên nên chắc chắn các biến không null ở đây
        final studentData = json.decode(studentDataStr!);
        final examsData = json.decode(examsDataStr!);
        final testPaperDetails = json.decode(testPaperStr!);

        // Tạo đối tượng kết quả mới
        final resultData = {
          'student_info': {
            'student_code': studentData['code'],
            'name': studentData['name'],
            'major': studentData['majors']
                ?.firstWhere((m) => m['is_main'] == 1)['name'],
            'exam_code': examsData[0]['exam_code'],
          },
          'exam_info': {
            'test_session': {
              'name': examsData[0]['test_session']['name'],
              'start_date': examsData[0]['test_session']['start_date'],
              'end_date': examsData[0]['test_session']['end_date'],
            },
            'subject': {
              'name': examsData[0]['subject']['name'],
              'code': examsData[0]['subject']['code'],
            },
            'shift': {
              'name': examsData[0]['room']['shift']['name'],
              'start_time': examsData[0]['room']['shift']['start_time'],
              'end_time': examsData[0]['room']['shift']['end_time'],
            },
            'room': {
              'name': examsData[0]['room']['name'],
              'location': examsData[0]['room']['location'],
            },
            'test_paper': {
              'id': testPaperDetails['id'],
              'name': testPaperDetails['name'],
              'subject': testPaperDetails['subject'],
              'duration': testPaperDetails['duration'],
              'total_questions': testPaperDetails['total_questions'],
              'difficulty_rates': testPaperDetails['difficulty_rates'],
              'tags': testPaperDetails['tags'],
            },
          },
          'result': {
            'total_questions': widget.totalQuestions,
            'correct_answers': widget.correctAnswers,
            'score': widget.score,
            'submitted_at': DateTime.now().toIso8601String(),
          },
        };

        try {
          // Chuyển đối tượng thành JSON và mã hóa
          final resultJson = json.encode(resultData);
          final encrypted =
              AppCrypto.encrypter.encrypt(resultJson, iv: AppCrypto.iv);
          await file.writeAsBytes(encrypted.bytes);

          if (mounted) {
            showDialog(
              context: context,
              builder: (context) => ContentDialog(
                title: const Text('Thành công'),
                content: Text('Đã lưu kết quả vào file:\n${file.path}'),
                actions: [
                  Button(
                    child: const Text('Đóng'),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            );
          }
        } catch (e) {
          throw Exception('Lỗi khi mã hóa và lưu file: $e');
        }
      }
    } catch (e) {
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể lưu kết quả: ${e.toString()}'),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    }
  }

  Widget _buildStudentInfo(BuildContext context) {
    return FutureBuilder<SharedPreferences>(
      future: SharedPreferences.getInstance(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) {
          return const Center(child: ProgressRing());
        }

        final prefs = snapshot.data!;
        final studentDataStr = prefs.getString('student_data');
        final examsDataStr = prefs.getString('exams_data');
        final serverUrl = prefs.getString('server_url');

        final studentData =
            studentDataStr != null ? json.decode(studentDataStr) : null;
        final examsData =
            examsDataStr != null ? json.decode(examsDataStr) : null;

        // Cập nhật URL avatar
        if (studentData?['avatar_url'] != null && serverUrl != null) {
          String avatarUrl = studentData!['avatar_url'];
          if (avatarUrl.startsWith('/')) {
            avatarUrl = avatarUrl.substring(1);
          }
          studentData['avatar_url'] = '$serverUrl/$avatarUrl';
        }

        return Row(
          children: [
            // Avatar
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: studentData?['avatar_url'] != null
                  ? Image.network(
                      studentData!['avatar_url'],
                      width: 100,
                      height: 120,
                      fit: BoxFit.cover,
                      headers: {
                        'Accept': 'image/*',
                      },
                      loadingBuilder: (context, child, loadingProgress) {
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
                        // print('🔍 URL ảnh: ${studentData!['avatar_url']}');
                        return Container(
                          width: 100,
                          height: 120,
                          color: Colors.grey[40],
                          child: const Icon(FluentIcons.contact, size: 48),
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
            // Thông tin thí sinh
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Thông tin thí sinh',
                    style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Text('Mã sinh viên: ${studentData?['code'] ?? ''}'),
                Text('Họ và tên: ${studentData?['name'] ?? ''}'),
                Text(
                    'Chuyên ngành: ${studentData?['majors']?.firstWhere((m) => m['is_main'] == 1)['name'] ?? ''}'),
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
                if (examsData != null && examsData.isNotEmpty) ...[
                  Text('Kỳ thi: ${examsData[0]['test_session']['name']}'),
                  Text('Môn thi: ${examsData[0]['subject']['name']}'),
                  Text(
                      'Phòng thi: ${examsData[0]['room']['name']} - ${examsData[0]['room']['location']}'),
                  Text('Ca thi: ${examsData[0]['room']['shift']['name']}'),
                ],
              ],
            ),
          ],
        );
      },
    );
  }

  Future<void> _exportResult(BuildContext context,
      Map<String, dynamic> studentData, List<dynamic> examsData) async {
    try {
      final appDir = Directory.current;
      final resultDir = Directory('${appDir.path}\\Results');

      if (!await resultDir.exists()) {
        await resultDir.create();
      }

      final now = DateTime.now();
      final formatter = DateFormat('dd-MM-yyyy_HH-mm');
      final fileName =
          'ket_qua_thi_${studentData['code']}_${formatter.format(now)}.edudex';
      final file = File('${resultDir.path}\\$fileName');

      final buffer = StringBuffer();
      buffer.writeln('KẾT QUẢ BÀI THI');
      buffer.writeln('==============');
      buffer.writeln();

      // Thông tin thí sinh
      buffer.writeln('THÔNG TIN THÍ SINH');
      buffer.writeln('Mã sinh viên: ${studentData['code']}');
      buffer.writeln('Họ và tên: ${studentData['name']}');
      buffer.writeln(
          'Chuyên ngành: ${studentData['majors'].firstWhere((m) => m['is_main'] == 1)['name']}');
      buffer.writeln();

      // Thông tin bài thi
      if (examsData.isNotEmpty) {
        buffer.writeln('THÔNG TIN BÀI THI');
        buffer.writeln('Kỳ thi: ${examsData[0]['test_session']['name']}');
        buffer.writeln('Môn thi: ${examsData[0]['subject']['name']}');
        buffer.writeln(
            'Phòng thi: ${examsData[0]['room']['name']} - ${examsData[0]['room']['location']}');
        buffer.writeln('Ca thi: ${examsData[0]['room']['shift']['name']}');
        buffer.writeln();
      }

      // Kết quả
      buffer.writeln('KẾT QUẢ');
      buffer.writeln('Tổng số câu hỏi: ${widget.totalQuestions}');
      buffer.writeln('Số câu trả lời đúng: ${widget.correctAnswers}');
      buffer.writeln('Điểm số: ${widget.score.toStringAsFixed(1)}');
      buffer.writeln('Kết quả: ${widget.score >= 5.0 ? 'Đạt' : 'Không đạt'}');
      buffer.writeln();

      // Chi tiết bài làm dạng JSON đơn giản hơn
      final detailsJson = {
        'questions': widget.questions.asMap().entries.map((entry) {
          final index = entry.key;
          final question = entry.value;
          final selectedAnswerIndex = widget.userAnswers[index];
          final answers = question['answers'] as List;

          return {
            'id': question['id'],
            'content': question['content'],
            'level': question['level'],
            'selected_answer': selectedAnswerIndex != null
                ? {
                    'id': answers[selectedAnswerIndex]['id'],
                    'content': answers[selectedAnswerIndex]['content'],
                  }
                : null,
          };
        }).toList(),
      };

      buffer.writeln('\nCHI TIẾT BÀI LÀM');
      buffer.writeln('===============');
      buffer.writeln(const JsonEncoder.withIndent('  ').convert(detailsJson));
      buffer.writeln();

      buffer.writeln('Thời gian xuất kết quả: ${formatter.format(now)}');

      try {
        final plainText = buffer.toString();
        log('Plain text length: ${plainText.length}');

        final encrypted =
            AppCrypto.encrypter.encrypt(plainText, iv: AppCrypto.iv);
        log('Encrypted bytes length: ${encrypted.bytes.length}');

        await file.writeAsBytes(encrypted.bytes);
      } catch (e) {
        log('Encryption error: $e');
        rethrow;
      }

      if (context.mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Thành công'),
            content: Text('Đã xuất kết quả thi vào file:\n${file.path}'),
            actions: [
              Button(
                child: const Text('OK'),
                onPressed: () => Navigator.pop(context),
              ),
              FilledButton(
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(FluentIcons.folder_open),
                    SizedBox(width: 8),
                    Text('Mở thư mục'),
                  ],
                ),
                onPressed: () async {
                  Navigator.pop(context);
                  try {
                    var shell = Shell();
                    // Mở thư mục chứa file
                    await shell.run('explorer "${file.parent.path}"');
                  } catch (e) {
                    log('Error opening folder: $e');
                    if (context.mounted) {
                      showDialog(
                        context: context,
                        builder: (context) => ContentDialog(
                          title: const Text('Lỗi'),
                          content: const Text('Không thể mở thư mục'),
                          actions: [
                            Button(
                              child: const Text('OK'),
                              onPressed: () => Navigator.pop(context),
                            ),
                          ],
                        ),
                      );
                    }
                  }
                },
              ),
            ],
          ),
        );
      }
    } catch (e) {
      log('Export error: $e');
      if (context.mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể xuất kết quả: ${e.toString()}'),
            actions: [
              Button(
                child: const Text('OK'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    }
  }

  String _getDifficultyText(int level) {
    switch (level) {
      case 1:
        return 'Dễ';
      case 2:
        return 'Trung bình';
      case 3:
        return 'Khó';
      default:
        return 'Không xác định';
    }
  }

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();
    final bool isPassed = widget.score >= 5.0;

    return NavigationView(
      appBar: NavigationAppBar(
        automaticallyImplyLeading: false,
        title: () {
          return const DragToMoveArea(
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              child: Text('Kết quả bài làm'),
            ),
          );
        }(),
        actions: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            Align(
              alignment: AlignmentDirectional.centerEnd,
              child: Padding(
                padding: const EdgeInsetsDirectional.only(end: 8.0),
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
            ),
            const WindowButtons(),
          ],
        ),
      ),
      content: ScaffoldPage(
        padding: EdgeInsets.zero,
        content: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border(bottom: BorderSide(color: Colors.grey[30]!)),
              ),
              child: _buildStudentInfo(context),
            ),

            // Kết quả
            Expanded(
              child: Center(
                child: Container(
                  constraints: const BoxConstraints(maxWidth: 800),
                  padding: const EdgeInsets.all(32),
                  child: Card(
                    padding: const EdgeInsets.all(32),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Text(
                          'Kết quả bài làm',
                          style: TextStyle(
                            fontSize: 32,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 48),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildResultItem(
                              'Tổng số câu hỏi',
                              widget.totalQuestions.toString(),
                              Colors.blue,
                            ),
                            _buildResultItem(
                              'Số câu trả lời đúng',
                              widget.correctAnswers.toString(),
                              Colors.green,
                            ),
                            _buildResultItem(
                              'Số điểm',
                              widget.score.toStringAsFixed(1),
                              Colors.orange,
                            ),
                          ],
                        ),
                        const SizedBox(height: 48),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Text(
                              'Kết quả: ',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              isPassed ? 'Đạt' : 'Không đạt',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: isPassed ? Colors.green : Colors.red,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 48),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            FilledButton(
                              onPressed: () {
                                Navigator.of(context).pushAndRemoveUntil(
                                  FluentPageRoute(
                                    builder: (context) =>
                                        const DashboardScreen(),
                                  ),
                                  (route) => false,
                                );
                              },
                              child: const Padding(
                                padding: EdgeInsets.symmetric(
                                  horizontal: 32,
                                  vertical: 12,
                                ),
                                child: Text(
                                  'Kết thúc',
                                  style: TextStyle(fontSize: 16),
                                ),
                              ),
                            ),
                            const SizedBox(width: 16),
                            Button(
                              onPressed: () async {
                                await _saveResult();
                              },
                              child: const Padding(
                                padding: EdgeInsets.symmetric(
                                  horizontal: 32,
                                  vertical: 12,
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(FluentIcons.download),
                                    SizedBox(width: 8),
                                    Text(
                                      'Xuất kết quả',
                                      style: TextStyle(fontSize: 16),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResultItem(String label, String value, AccentColor color) {
    return Column(
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(
            horizontal: 24,
            vertical: 16,
          ),
          decoration: BoxDecoration(
            color: color.lightest,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            value,
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ),
      ],
    );
  }
}
