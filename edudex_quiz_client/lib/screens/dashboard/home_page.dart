import 'package:fluent_ui/fluent_ui.dart';
import 'package:edudex_quiz_client/screens/quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  Map<String, dynamic>? _studentData;
  List<dynamic>? _examsData;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
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

          if (_studentData!['avatar_url'] != null) {
            String avatarUrl = _studentData!['avatar_url'];
            if (avatarUrl.startsWith('/')) {
              avatarUrl = avatarUrl.substring(1);
            }
            String baseUrl = serverUrl;
            while (baseUrl.endsWith('/')) {
              baseUrl = baseUrl.substring(0, baseUrl.length - 1);
            }
            _studentData!['avatar_url'] = '$baseUrl/$avatarUrl';
            print('🖼️ Avatar URL: ${_studentData!['avatar_url']}');
          }

          _isLoading = false;
        });
      }
    } catch (e) {
      print('Lỗi khi tải dữ liệu: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: ProgressRing());
    }

    final student = _studentData!;
    final exams = _examsData!;

    return ScaffoldPage(
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      content: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome section
            Card(
              padding: const EdgeInsets.all(24.0),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Chào mừng, ${student['name']}!',
                          style: FluentTheme.of(context).typography.titleLarge,
                        ),
                        const SizedBox(height: 8),
                        if (exams.isNotEmpty) ...[
                          Text(
                            'Bạn có bài kiểm tra môn ${exams[0]['subject']['name']}',
                            style: FluentTheme.of(context).typography.body,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Phòng: ${exams[0]['room']['name']} - ${exams[0]['room']['location']}',
                            style: FluentTheme.of(context).typography.body,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Ca thi: ${exams[0]['room']['shift']['name']}',
                            style: FluentTheme.of(context).typography.body,
                          ),
                        ] else
                          const Text(
                            'Không có bài kiểm tra nào',
                            style: TextStyle(color: Colors.warningPrimaryColor),
                          ),
                      ],
                    ),
                  ),
                  if (exams.isNotEmpty)
                    FilledButton(
                      child: const Text('Vào thi'),
                      onPressed: () {
                        Navigator.push(
                          context,
                          FluentPageRoute(
                            builder: (context) => const QuizScreen(),
                          ),
                        );
                      },
                    ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Thông tin sinh viên
            Text(
              'Thông tin sinh viên',
              style: FluentTheme.of(context).typography.subtitle,
            ),
            const SizedBox(height: 16),
            Card(
              padding: const EdgeInsets.all(16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Thêm ảnh thí sinh
                  Column(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: student['avatar_url'] != null
                            ? Image.network(
                                student['avatar_url'],
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
                                      child: ProgressRing(
                                        strokeWidth: 3,
                                      ),
                                    ),
                                  );
                                },
                                errorBuilder: (context, error, stackTrace) {
                                  print('❌ Lỗi tải ảnh: $error');
                                  print('🔍 URL ảnh: ${student['avatar_url']}');
                                  return Container(
                                    width: 100,
                                    height: 120,
                                    color: Colors.grey[40],
                                    child: const Icon(FluentIcons.contact,
                                        size: 48),
                                  );
                                },
                              )
                            : Container(
                                width: 100,
                                height: 120,
                                color: Colors.grey[40],
                                child:
                                    const Icon(FluentIcons.contact, size: 48),
                              ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        student['code'],
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 24),
                  // Thông tin chi tiết
                  Expanded(
                    child: Column(
                      children: [
                        _buildInfoRow('Họ và tên', student['name']),
                        _buildInfoRow('Email', student['email']),
                        _buildInfoRow('Số điện thoại', student['phone']),
                        _buildInfoRow('Ngày sinh', student['birthday']),
                        _buildInfoRow('Địa chỉ', student['address']),
                        _buildInfoRow(
                          'Chuyên ngành chính',
                          student['majors']
                              .firstWhere((m) => m['is_main'] == 1)['name'],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Thông tin kỳ thi
            if (exams.isNotEmpty) ...[
              Text(
                'Thông tin kỳ thi',
                style: FluentTheme.of(context).typography.subtitle,
              ),
              const SizedBox(height: 16),
              Card(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    _buildInfoRow(
                      'Kỳ thi',
                      exams[0]['test_session']['name'],
                    ),
                    _buildInfoRow(
                      'Môn thi',
                      '${exams[0]['subject']['code']} - ${exams[0]['subject']['name']}',
                    ),
                    _buildInfoRow('Mã đề thi', exams[0]['exam_code']),
                    _buildInfoRow(
                      'Thời gian thi',
                      '${exams[0]['room']['shift']['start_time'].substring(11, 16)} - ${exams[0]['room']['shift']['end_time'].substring(11, 16)}',
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 150,
            child: Text(
              label,
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(
            child: Text(value),
          ),
        ],
      ),
    );
  }
}
