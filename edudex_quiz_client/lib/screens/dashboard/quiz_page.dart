import 'package:fluent_ui/fluent_ui.dart';
import '../quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

class QuizMenuPage extends StatefulWidget {
  const QuizMenuPage({super.key});

  @override
  State<QuizMenuPage> createState() => _QuizMenuPageState();
}

class _QuizMenuPageState extends State<QuizMenuPage> {
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _examInfo;

  @override
  void initState() {
    super.initState();
    _loadExamInfo();
  }

  Future<void> _loadExamInfo() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final teacherIp = prefs.getString('teacher_ip');

      if (token == null || teacherIp == null) {
        throw Exception('Không tìm thấy thông tin đăng nhập');
      }

      final response = await http.get(
        Uri.parse('http://$teacherIp:8689/exam-info'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      print('📥 Status code: ${response.statusCode}');
      print('📄 Response: ${response.body}');

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _examInfo = data['data'];
        });
      } else {
        setState(() {
          _errorMessage = data['message'] ?? 'Không thể tải thông tin đề thi';
        });
      }
    } catch (e) {
      print('❌ Error: $e');
      setState(() {
        _errorMessage = 'Đã có lỗi xảy ra: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: ProgressRing());
    }

    if (_errorMessage != null) {
      return Center(
        child: InfoBar(
          title: Text(_errorMessage!),
          severity: InfoBarSeverity.error,
        ),
      );
    }

    if (_examInfo == null) {
      return const Center(child: Text('Không có thông tin đề thi'));
    }

    final subject = _examInfo!['subject'];
    final exam = _examInfo!['exam'];
    final shift = _examInfo!['shift'];
    final room = _examInfo!['room'];

    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Thông tin bài thi'),
      ),
      content: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      subject['name'],
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text('Mã môn: ${subject['code']}'),
                    const SizedBox(height: 24),
                    Text('Tên đề thi: ${exam['name']}'),
                    Text('Thời gian làm bài: ${exam['duration']} phút'),
                    Text('Số câu hỏi: ${exam['total_questions']} câu'),
                    if (exam['description'] != null)
                      Text('Mô tả: ${exam['description']}'),
                    const SizedBox(height: 24),
                    Text('Ca thi: ${shift['name']}'),
                    Text(
                      'Thời gian: ${shift['start_time']} - ${shift['end_time']}',
                    ),
                    const SizedBox(height: 24),
                    Text('Phòng thi: ${room['name']}'),
                    Text('Địa điểm: ${room['facility']}'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () {
                Navigator.pushReplacement(
                  context,
                  FluentPageRoute(
                    builder: (context) => QuizScreen(
                      examInfo: _examInfo!,
                    ),
                  ),
                );
              },
              child: const Text('Bắt đầu làm bài'),
            ),
          ],
        ),
      ),
    );
  }
}
