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
  Map<String, dynamic>? _testPaperDetails;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadExamData();
  }

  Future<void> _loadExamData() async {
    try {
      setState(() => _isLoading = true);

      final prefs = await SharedPreferences.getInstance();
      final examsDataStr = prefs.getString('exams_data');
      final token = prefs.getString('token');
      final serverUrl = prefs.getString('server_url');

      if (examsDataStr != null && token != null && serverUrl != null) {
        final examsData = json.decode(examsDataStr);
        if (examsData.isNotEmpty) {
          final testPaperId = examsData[0]['test_paper']['id'];
          final response = await http.get(
            Uri.parse('$serverUrl/api/student/test-papers/$testPaperId'),
            headers: {
              'Authorization': 'copecute $token',
              'Accept': 'application/json',
            },
          );

          final data = json.decode(response.body);

          switch (response.statusCode) {
            case 200:
              setState(() {
                _testPaperDetails = data['test_paper'];
                _isLoading = false;
              });
              break;

            case 403:
              setState(() => _isLoading = false);
              if (mounted) {
                showDialog(
                  context: context,
                  builder: (context) => ContentDialog(
                    title: const Text('Thông báo'),
                    content: Text(data['message'] ??
                        'Chưa đến giờ thi hoặc đã hết giờ thi'),
                    actions: [
                      Button(
                        child: const Text('OK'),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                );
              }
              break;

            case 401:
              setState(() => _isLoading = false);
              if (mounted) {
                showDialog(
                  context: context,
                  builder: (context) => ContentDialog(
                    title: const Text('Lỗi xác thực'),
                    content: const Text(
                        'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'),
                    actions: [
                      Button(
                        child: const Text('OK'),
                        onPressed: () {
                          Navigator.pop(context);
                          // TODO: Chuyển về trang đăng nhập
                        },
                      ),
                    ],
                  ),
                );
              }
              break;

            default:
              setState(() => _isLoading = false);
              if (mounted) {
                showDialog(
                  context: context,
                  builder: (context) => ContentDialog(
                    title: const Text('Lỗi'),
                    content: Text(data['message'] ?? 'Đã có lỗi xảy ra'),
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
      }
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể tải thông tin đề thi: ${e.toString()}'),
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

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: ProgressRing());
    }

    if (_testPaperDetails == null) {
      return const Center(child: Text('Không có thông tin đề thi'));
    }

    final testPaper = _testPaperDetails!;
    final subject = testPaper['subject'];
    final room = testPaper['room'];
    final shift = testPaper['shift'];

    return ScaffoldPage(
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      content: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              width: double.infinity,
              child: Card(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Thông tin cơ bản',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    InfoLabel(
                      label: 'Tên đề thi',
                      child: Text(testPaper['name']),
                    ),
                    InfoLabel(
                      label: 'Môn thi',
                      child: Text(subject['name']),
                    ),
                    InfoLabel(
                      label: 'Thời gian làm bài',
                      child: Text('${testPaper['duration']} phút'),
                    ),
                    InfoLabel(
                      label: 'Số câu hỏi',
                      child: Text('${testPaper['total_questions']} câu'),
                    ),
                    InfoLabel(
                      label: 'Phòng thi',
                      child:
                          Text('${room['name']} - ${room['location']['name']}'),
                    ),
                    InfoLabel(
                      label: 'Ca thi',
                      child: Text(
                          '${shift['name']} (${formatTime(shift['start_time'])} - ${formatTime(shift['end_time'])})'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: Card(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Phân bố câu hỏi',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    InfoLabel(
                      label: 'Tỷ lệ độ khó',
                      child: Text(
                        'Dễ: ${testPaper['difficulty_rates']['easy']}% | '
                        'Trung bình: ${testPaper['difficulty_rates']['medium']}% | '
                        'Khó: ${testPaper['difficulty_rates']['hard']}%',
                      ),
                    ),
                    const SizedBox(height: 8),
                    InfoLabel(
                      label: 'Phân bố theo chủ đề',
                    ),
                    ...testPaper['tags'].map((tag) {
                      final questionsLevel = tag['questions_by_level'];
                      final rates = tag['rates'];
                      return Padding(
                        padding: const EdgeInsets.only(left: 8, top: 8),
                        child: Text(
                          '${tag['name']}: ${tag['total_questions']} câu '
                          '(Dễ: ${questionsLevel['easy']}, TB: ${questionsLevel['medium']}, Khó: ${questionsLevel['hard']}) '
                          '(${rates['easy']}/${rates['medium']}/${rates['hard']})',
                        ),
                      );
                    }).toList(),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            Center(
              child: FilledButton(
                child: const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                  child: Text(
                    'Bắt đầu làm bài',
                    style: TextStyle(fontSize: 16),
                  ),
                ),
                onPressed: () async {
                  try {
                    final prefs = await SharedPreferences.getInstance();
                    final token = prefs.getString('token');
                    final serverUrl = prefs.getString('server_url');

                    if (token == null || serverUrl == null) {
                      throw Exception('Không tìm thấy thông tin đăng nhập');
                    }

                    // Kiểm tra trước khi vào thi
                    final response = await http.get(
                      Uri.parse(
                          '$serverUrl/api/student/test-papers/${testPaper['id']}/questions'),
                      headers: {
                        'Authorization': 'copecute $token',
                        'Accept': 'application/json',
                      },
                    );

                    final data = json.decode(response.body);

                    if (response.statusCode == 200 && data['success'] == true) {
                      if (mounted) {
                        Navigator.push(
                          context,
                          FluentPageRoute(
                            builder: (context) => QuizScreen(
                              testSessionSubjectId: testPaper['id'],
                            ),
                          ),
                        );
                      }
                    } else {
                      if (mounted) {
                        showDialog(
                          context: context,
                          builder: (context) => ContentDialog(
                            title: const Text('Lỗi'),
                            content: Text(
                                data['message'] ?? 'Không thể bắt đầu bài thi'),
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
                  } catch (e) {
                    if (mounted) {
                      showDialog(
                        context: context,
                        builder: (context) => ContentDialog(
                          title: const Text('Lỗi'),
                          content: Text('Đã có lỗi xảy ra: ${e.toString()}'),
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
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  String formatTime(String timeStr) {
    final dateTime = DateTime.parse(timeStr);
    return '${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
  }
}
