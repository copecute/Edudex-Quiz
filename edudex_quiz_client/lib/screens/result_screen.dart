import 'package:fluent_ui/fluent_ui.dart';
import 'package:window_manager/window_manager.dart';
import 'package:provider/provider.dart';
import '../theme.dart';
import 'package:edudex_quiz_client/screens/dashboard/dashboard_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class ResultScreen extends StatelessWidget {
  final int totalQuestions;
  final int correctAnswers;
  final double score;

  const ResultScreen({
    super.key,
    required this.totalQuestions,
    required this.correctAnswers,
    required this.score,
  });

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

        final studentData =
            studentDataStr != null ? json.decode(studentDataStr) : null;
        final examsData =
            examsDataStr != null ? json.decode(examsDataStr) : null;

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
                            child: ProgressRing(
                              strokeWidth: 3,
                            ),
                          ),
                        );
                      },
                      errorBuilder: (context, error, stackTrace) => Container(
                        width: 100,
                        height: 120,
                        color: Colors.grey[40],
                        child: const Icon(FluentIcons.contact, size: 48),
                      ),
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

  @override
  Widget build(BuildContext context) {
    final appTheme = context.watch<AppTheme>();
    final bool isPassed = score >= 5.0;

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
                  constraints: const BoxConstraints(maxWidth: 600),
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
                              totalQuestions.toString(),
                              Colors.blue,
                            ),
                            _buildResultItem(
                              'Số câu trả lời đúng',
                              correctAnswers.toString(),
                              Colors.green,
                            ),
                            _buildResultItem(
                              'Số điểm',
                              score.toStringAsFixed(1),
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
                        FilledButton(
                          onPressed: () {
                            Navigator.of(context).pushAndRemoveUntil(
                              FluentPageRoute(
                                builder: (context) => const DashboardScreen(),
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
