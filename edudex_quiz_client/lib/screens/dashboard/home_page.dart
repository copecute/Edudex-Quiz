import 'package:fluent_ui/fluent_ui.dart';
import 'package:edudex_quiz_client/screens/quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<SharedPreferences>(
      future: SharedPreferences.getInstance(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) {
          return const Center(child: ProgressRing());
        }

        final prefs = snapshot.data!;
        final studentDataStr = prefs.getString('student_data');

        if (studentDataStr == null) {
          return const Center(child: Text('Không có dữ liệu'));
        }

        final studentData = json.decode(studentDataStr);

        return ScaffoldPage(
          padding: const EdgeInsets.symmetric(horizontal: 24.0),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Thông tin cá nhân
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'Thông tin thí sinh',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.green.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(16),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    FluentIcons.circle_fill,
                                    size: 8,
                                    color: Colors.successPrimaryColor,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Số máy: ${prefs.getString('may_so') ?? ''}',
                                    style: const TextStyle(
                                        color: Colors.successPrimaryColor),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),
                        Wrap(
                          spacing: 48,
                          runSpacing: 24,
                          children: [
                            _buildInfoGroup(
                              'Thông tin cá nhân',
                              [
                                _buildInfoRow(
                                    'Họ và tên:', studentData['full_name']),
                                _buildInfoRow(
                                    'Ngày sinh:',
                                    DateTime.parse(studentData['date_of_birth'])
                                        .toLocal()
                                        .toString()
                                        .split(' ')[0]
                                        .split('-')
                                        .reversed
                                        .join('-')),
                                _buildInfoRow(
                                    'Giới tính:', studentData['gender']),
                                _buildInfoRow(
                                    'Địa chỉ:', studentData['address']),
                              ],
                            ),
                            _buildInfoGroup(
                              'Thông tin dự thi',
                              [
                                _buildInfoRow(
                                    'Số báo danh:', studentData['exam_code']),
                                _buildInfoRow('Mã sinh viên:',
                                    studentData['student_code']),
                                _buildInfoRow('Số ghế:',
                                    studentData['seat_number'].toString()),
                                _buildInfoRow(
                                    'Số điện thoại:', studentData['phone']),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                // Thông tin bài thi
                FutureBuilder<Map<String, dynamic>?>(
                  future: _loadExamInfo(),
                  builder: (context, snapshot) {
                    if (snapshot.connectionState == ConnectionState.waiting) {
                      return const Center(child: ProgressRing());
                    }

                    if (snapshot.hasError) {
                      return InfoBar(
                        title: const Text('Lỗi kết nối'),
                        content: Text(
                          snapshot.error.toString(),
                          style: const TextStyle(height: 1.5),
                        ),
                        severity: InfoBarSeverity.error,
                        isLong: true,
                      );
                    }

                    final examInfo = snapshot.data;
                    if (examInfo == null) {
                      return const InfoBar(
                        title: Text('Không có thông tin đề thi'),
                        severity: InfoBarSeverity.warning,
                      );
                    }

                    final subject = examInfo['subject'];
                    final exam = examInfo['exam'];
                    final shift = examInfo['shift'];
                    final room = examInfo['room'];

                    return Card(
                      child: Padding(
                        padding: const EdgeInsets.all(24.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  subject['name'],
                                  style: const TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                FilledButton(
                                  onPressed: () {
                                    Navigator.pushReplacement(
                                      context,
                                      FluentPageRoute(
                                        builder: (context) => QuizScreen(
                                          examInfo: examInfo,
                                        ),
                                      ),
                                    );
                                  },
                                  child: const Row(
                                    children: [
                                      Icon(FluentIcons.play),
                                      SizedBox(width: 8),
                                      Text('Bắt đầu làm bài'),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 24),
                            Wrap(
                              spacing: 48,
                              runSpacing: 24,
                              children: [
                                _buildInfoGroup(
                                  'Thông tin môn thi',
                                  [
                                    // _buildInfoRow('Mã môn:', subject['code']),
                                    _buildInfoRow('Tên đề thi:', exam['name']),
                                    _buildInfoRow('Thời gian:',
                                        '${exam['duration']} phút'),
                                    _buildInfoRow('Số câu hỏi:',
                                        '${exam['total_questions']} câu'),
                                    if (exam['description'] != null)
                                      _buildInfoRow(
                                          'Mô tả:', exam['description']),
                                  ],
                                ),
                                _buildInfoGroup(
                                  'Thông tin ca thi',
                                  [
                                    _buildInfoRow('Ca thi:', shift['name']),
                                    _buildInfoRow(
                                        'Ngày thi:',
                                        DateFormat('dd/MM/yyyy').format(
                                            DateTime.parse(
                                                shift['start_time']))),
                                    _buildInfoRow('Thời gian:',
                                        '${DateFormat('HH:mm').format(DateTime.parse(shift['start_time']))} - ${DateFormat('HH:mm').format(DateTime.parse(shift['end_time']))} (${shift['duration']} phút)'),
                                    _buildInfoRow('Phòng thi:', room['name']),
                                    _buildInfoRow(
                                        'Địa điểm:', room['facility']),
                                  ],
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildInfoGroup(String title, List<Widget> children) {
    return SizedBox(
      width: 400,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: const TextStyle(color: Colors.grey),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }

  Future<Map<String, dynamic>?> _loadExamInfo() async {
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

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        return data['data'];
      } else {
        throw Exception(data['message'] ?? 'Không thể tải thông tin đề thi');
      }
    } catch (e) {
      String errorMessage = 'Đã có lỗi xảy ra';

      // xử lý các loại lỗi
      if (e.toString().contains('SocketException')) {
        errorMessage = 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra:\n'
            '• Máy chủ đã được bật chưa\n'
            '• Kết nối mạng có ổn định không\n'
            '• Địa chỉ IP máy chủ có chính xác không';
      } else if (e.toString().contains('refused')) {
        errorMessage = 'Máy chủ từ chối kết nối. Vui lòng:\n'
            '• Kiểm tra phần mềm máy chủ có đang chạy không\n'
            '• Liên hệ cán bộ kỹ thuật để được hỗ trợ';
      }

      return Future.error(errorMessage);
    }
  }
}
