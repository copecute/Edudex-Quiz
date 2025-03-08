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
import 'package:encrypt/encrypt.dart';

class ResultScreen extends StatefulWidget {
  final int totalQuestions;
  final int correctAnswers;
  final double score;
  final List<dynamic> questions;
  final Map<int, int> userAnswers;
  final String submissionFile;
  final List<String> actionLogs;
  final Map<String, dynamic> studentInfo;
  final Map<String, dynamic> examInfo;

  const ResultScreen({
    super.key,
    required this.totalQuestions,
    required this.correctAnswers,
    required this.score,
    required this.questions,
    required this.userAnswers,
    required this.submissionFile,
    required this.actionLogs,
    required this.studentInfo,
    required this.examInfo,
  });

  @override
  State<ResultScreen> createState() => _ResultScreenState();
}

class _ResultScreenState extends State<ResultScreen> {
  Future<void> _saveResult() async {
    try {
      final now = DateTime.now();
      final formatter = DateFormat('dd-MM-yyyy_HH-mm');
      final baseFileName =
          'ket_qua_thi_${widget.studentInfo['code'] ?? 'unknown'}_${formatter.format(now)}';

      // Sử dụng submissionFile đã được truyền từ quiz_screen (đã là base64 được mã hóa)
      final submissionContent = widget.submissionFile;

      // Lưu file mã hóa .edudex
      final encryptedResult = await FilePicker.platform.saveFile(
        dialogTitle: 'Lưu kết quả bài thi (Mã hóa)',
        fileName: '$baseFileName.edudex',
        allowedExtensions: ['edudex'],
        type: FileType.custom,
      );

      if (encryptedResult != null) {
        final encryptedFile = File(encryptedResult);

        // Lưu trực tiếp nội dung đã mã hóa
        // Chuyển base64 thành bytes để lưu vào file
        final bytes = base64Decode(submissionContent);
        await encryptedFile.writeAsBytes(bytes);
      }

      if (mounted && (encryptedResult != null)) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Thành công'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (encryptedResult != null)
                  Text('Đã lưu tệp tin:\n${encryptedResult}'),
              ],
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
    } catch (e) {
      print('❌ Lỗi lưu kết quả: $e');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể lưu kết quả:\n${e.toString()}'),
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
    return Row(
      children: [
        // Thông tin thí sinh
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Thông tin thí sinh',
                style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text('Mã sinh viên: ${widget.studentInfo['code'] ?? ''}'),
            Text('Họ và tên: ${widget.studentInfo['name'] ?? ''}'),
            Text('Số báo danh: ${widget.studentInfo['exam_code'] ?? ''}'),
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
            Text('Kỳ thi: ${widget.examInfo['test_session']['name'] ?? ''}'),
            Text(
                'Môn thi: ${widget.examInfo['subject']['name'] ?? ''} (${widget.examInfo['subject']['code'] ?? ''})'),
            Text(
                'Phòng thi: ${widget.examInfo['room']['name'] ?? ''} - ${widget.examInfo['room']['location'] ?? ''}'),
            Text('Ca thi: ${widget.examInfo['room']['shift']['name'] ?? ''}'),
          ],
        ),
      ],
    );
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
