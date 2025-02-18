import 'package:fluent_ui/fluent_ui.dart';
import '../quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:edudex_quiz_client/utils/cover_date_time.dart';

class QuizMenuPage extends StatefulWidget {
  const QuizMenuPage({super.key});

  @override
  State<QuizMenuPage> createState() => _QuizMenuPageState();
}

class _QuizMenuPageState extends State<QuizMenuPage> {
  Map<String, dynamic>? _testPaperDetails;
  List<dynamic>? _examsData;
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
          setState(() {
            _examsData = examsData;
          });

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
                          '${shift['name']} (${DateTimeHelper.formatTime(shift['start_time'])} - ${DateTimeHelper.formatTime(shift['end_time'])})'),
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
                        'Dễ: ${testPaper['difficulty_rates']['easy'].toStringAsFixed(1)}% | '
                        'Trung bình: ${testPaper['difficulty_rates']['medium'].toStringAsFixed(1)}% | '
                        'Khó: ${testPaper['difficulty_rates']['hard'].toStringAsFixed(1)}%',
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
                          '(${double.parse(rates['easy']).toStringAsFixed(1)}%/'
                          '${double.parse(rates['medium']).toStringAsFixed(1)}%/'
                          '${double.parse(rates['hard']).toStringAsFixed(1)}%)',
                        ),
                      );
                    }).toList(),
                    InfoLabel(
                      label: 'Thông tin thêm',
                      child: Text(
                        'Số câu hỏi theo chủ đề: ${testPaper['questions_by_tags']} | '
                        'Số câu hỏi ngẫu nhiên: ${testPaper['questions_random']}',
                      ),
                    ),
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

                    print('📤 Request:');
                    print(
                        'URL: $serverUrl/api/student/test-papers/${testPaper['id']}/questions');
                    print('Headers: ${json.encode({
                          'Authorization': 'copecute $token',
                          'Accept': 'application/json',
                        })}');

                    // Kiểm tra trước khi vào thi
                    final response = await http.get(
                      Uri.parse(
                          '$serverUrl/api/student/test-papers/${testPaper['id']}/questions'),
                      headers: {
                        'Authorization': 'copecute $token',
                        'Accept': 'application/json',
                      },
                    );

                    print('📥 Response status: ${response.statusCode}');
                    print('📥 Response body: ${response.body}');

                    final data = json.decode(response.body);

                    print(
                        '🔍 Test session subject ID: ${_examsData![0]['test_session_subject_id']}');
                    print('🔍 Test paper ID: ${testPaper['id']}');
                    print('🔍 Exams data: ${json.encode(_examsData)}');

                    if (response.statusCode == 200 &&
                        data['test_paper']?['questions'] != null) {
                      if (mounted && _examsData != null) {
                        print('✅ Chuyển đến màn hình thi');
                        Navigator.push(
                          context,
                          FluentPageRoute(
                            builder: (context) => QuizScreen(
                              testSessionSubjectId: _examsData![0]
                                  ['test_session_subject_id'],
                            ),
                          ),
                        );
                      }
                    } else {
                      print('❌ Lỗi: ${data['message']}');
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
                    print('❌ Exception: $e');
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
    // Sử dụng DateTimeHelper để format thời gian sang GMT+7
    return DateTimeHelper.formatTime(timeStr);
  }
}
