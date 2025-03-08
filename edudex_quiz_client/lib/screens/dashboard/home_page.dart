import 'package:fluent_ui/fluent_ui.dart';
import 'package:edudex_quiz_client/screens/quiz_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
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
                        const Text(
                          'Thông tin cá nhân',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 24),
                        Row(
                          children: [
                            // Avatar
                            Container(
                              width: 120,
                              height: 120,
                              decoration: BoxDecoration(
                                color: Colors.grey[30],
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Center(
                                child: Icon(FluentIcons.contact, size: 64),
                              ),
                            ),
                            const SizedBox(width: 24),
                            // Thông tin
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _buildInfoRow(
                                      'Số báo danh:', studentData['exam_code']),
                                  _buildInfoRow('Mã sinh viên:',
                                      studentData['student_code']),
                                  _buildInfoRow(
                                      'Họ và tên:', studentData['full_name']),
                                  _buildInfoRow('Ngày sinh:',
                                      studentData['date_of_birth']),
                                  _buildInfoRow(
                                      'Giới tính:', studentData['gender']),
                                  _buildInfoRow(
                                      'Số điện thoại:', studentData['phone']),
                                  _buildInfoRow('Số ghế:',
                                      studentData['seat_number'].toString()),
                                  _buildInfoRow(
                                      'Địa chỉ:', studentData['address']),
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
