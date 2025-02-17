import 'package:fluent_ui/fluent_ui.dart';
import 'package:edudex_quiz_client/screens/quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:intl/intl.dart';
import 'package:edudex_quiz_client/utils/cover_date_time.dart';

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
        final examsDataStr = prefs.getString('exams_data');

        if (studentDataStr == null || examsDataStr == null) {
          return const Center(child: Text('Không có dữ liệu'));
        }

        final studentData = json.decode(studentDataStr);
        final examsData = json.decode(examsDataStr);

        return ScaffoldPage(
          header: const PageHeader(
            title: Text('Trang chủ'),
          ),
          content: SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Thông tin cá nhân
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Thông tin cá nhân',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            // Avatar
                            Container(
                              width: 100,
                              height: 120,
                              decoration: BoxDecoration(
                                color: Colors.grey[40],
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: studentData['avatar_url'] != null
                                  ? ClipRRect(
                                      borderRadius: BorderRadius.circular(4),
                                      child: Image.network(
                                        // Kết hợp server URL với đường dẫn avatar
                                        '${prefs.getString('server_url')}${studentData['avatar_url']}',
                                        fit: BoxFit.cover,
                                        errorBuilder:
                                            (context, error, stackTrace) {
                                          print(
                                              '❌ Error loading avatar: $error');
                                          return const Icon(FluentIcons.contact,
                                              size: 48);
                                        },
                                        loadingBuilder:
                                            (context, child, loadingProgress) {
                                          if (loadingProgress == null)
                                            return child;
                                          return const Center(
                                              child: ProgressRing());
                                        },
                                      ),
                                    )
                                  : const Icon(FluentIcons.contact, size: 48),
                            ),
                            const SizedBox(width: 16),
                            // Thông tin
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Mã sinh viên: ${studentData['code']}'),
                                Text('Họ và tên: ${studentData['name']}'),
                                Text('Email: ${studentData['email']}'),
                                Text('Số điện thoại: ${studentData['phone']}'),
                                Text('Ngày sinh: ${studentData['birthday']}'),
                                Text(
                                    'Giới tính: ${studentData['gender'] ? 'Nam' : 'Nữ'}'),
                                Text(
                                  'Chuyên ngành: ${studentData['majors']?.firstWhere((m) => m['is_main'] == 1)['name'] ?? ''}',
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Thông tin kỳ thi
                if (examsData.isNotEmpty)
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Thông tin kỳ thi',
                                      style: TextStyle(
                                        fontSize: 20,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      examsData[0]['test_session']['name'],
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      'Thời gian: ${DateTimeHelper.formatDateTime(examsData[0]['test_session']['start_date'])} - ${DateTimeHelper.formatDateTime(examsData[0]['test_session']['end_date'])}',
                                      style: const TextStyle(fontSize: 14),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          const Divider(),
                          const SizedBox(height: 16),
                          // Thông tin môn thi
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Cột trái
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Thông tin môn thi',
                                      style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    InfoLabel(
                                      label: 'Môn thi',
                                      labelStyle: const TextStyle(fontSize: 14),
                                      child: Padding(
                                        padding: const EdgeInsets.only(
                                            top: 4, bottom: 12),
                                        child: RichText(
                                          text: TextSpan(
                                            style: TextStyle(
                                              fontSize: 14,
                                              color: FluentTheme.of(context)
                                                  .typography
                                                  .body
                                                  ?.color,
                                            ),
                                            children: [
                                              const TextSpan(
                                                text: 'Môn thi: ',
                                                style: TextStyle(
                                                    fontWeight:
                                                        FontWeight.bold),
                                              ),
                                              TextSpan(
                                                text:
                                                    '${examsData[0]['subject']['name']} (${examsData[0]['subject']['code']})',
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                    InfoLabel(
                                      label: 'Số báo danh',
                                      labelStyle: const TextStyle(fontSize: 14),
                                      child: Padding(
                                        padding: const EdgeInsets.only(
                                            top: 4, bottom: 12),
                                        child: RichText(
                                          text: TextSpan(
                                            style: TextStyle(
                                              fontSize: 14,
                                              color: FluentTheme.of(context)
                                                  .typography
                                                  .body
                                                  ?.color,
                                            ),
                                            children: [
                                              const TextSpan(
                                                text: 'Số báo danh: ',
                                                style: TextStyle(
                                                    fontWeight:
                                                        FontWeight.bold),
                                              ),
                                              TextSpan(
                                                  text: examsData[0]
                                                      ['exam_code']),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 32),
                              // Cột phải
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const SizedBox(height: 34),
                                    InfoLabel(
                                      label: 'Đề thi',
                                      labelStyle: const TextStyle(fontSize: 14),
                                      child: Padding(
                                        padding: const EdgeInsets.only(
                                            top: 4, bottom: 12),
                                        child: Text(
                                          '${examsData[0]['test_paper']['name']} (${examsData[0]['test_paper']['duration']} phút - ${examsData[0]['test_paper']['total_questions']} câu)',
                                          style: const TextStyle(fontSize: 14),
                                        ),
                                      ),
                                    ),
                                    InfoLabel(
                                      label: 'Ca thi',
                                      labelStyle: const TextStyle(fontSize: 14),
                                      child: Padding(
                                        padding: const EdgeInsets.only(
                                            top: 4, bottom: 12),
                                        child: Text(
                                          '${examsData[0]['room']['shift']['name']} (${DateTimeHelper.formatTime(examsData[0]['room']['shift']['start_time'])} - ${DateTimeHelper.formatTime(examsData[0]['room']['shift']['end_time'])})',
                                          style: const TextStyle(fontSize: 14),
                                        ),
                                      ),
                                    ),
                                    InfoLabel(
                                      label: 'Phòng thi',
                                      labelStyle: const TextStyle(fontSize: 14),
                                      child: Padding(
                                        padding: const EdgeInsets.only(
                                            top: 4, bottom: 12),
                                        child: Text(
                                          '${examsData[0]['room']['name']} - ${examsData[0]['room']['location']}',
                                          style: const TextStyle(fontSize: 14),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }
}
