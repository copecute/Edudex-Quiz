import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../../providers/exam_provider.dart';
import '../../providers/student_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class QuizManagementPage extends StatefulWidget {
  const QuizManagementPage({super.key});

  @override
  State<QuizManagementPage> createState() => _QuizManagementPageState();
}

class _QuizManagementPageState extends State<QuizManagementPage> {
  final _examIdController = TextEditingController();
  Map<String, dynamic>? _examData;
  String? _errorMessage;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchExamData();
  }

  Future<void> _fetchExamData() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('user_token');
    final selectedShiftId = prefs.getInt('selected_shift_id');
    final selectedRoomId = prefs.getInt('selected_room_id');
    final serverUrl = prefs.getString('server_url');

    if (token == null ||
        selectedShiftId == null ||
        selectedRoomId == null ||
        serverUrl == null) {
      setState(() {
        _errorMessage = 'Thông tin không đầy đủ để lấy thông tin đề thi.';
        _isLoading = false;
      });
      return;
    }

    try {
      final url = Uri.parse(
          '$serverUrl/api/exam-schedule/shifts/$selectedShiftId/rooms/$selectedRoomId/exam');
      print('Đang lấy thông tin đề thi: $url');

      final response = await http.get(
        url,
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      final data = json.decode(response.body);

      if (data['status'] == 'success') {
        setState(() {
          _examData = data['data'];
          _errorMessage = null;
        });
      } else {
        setState(() {
          _errorMessage = data['message'] ?? 'Không thể lấy thông tin đề thi';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Lỗi khi lấy thông tin đề thi: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  void dispose() {
    _examIdController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Quản lý đề thi'),
      ),
      content: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: _isLoading
              ? const Center(
                  child: ProgressRing(),
                )
              : _errorMessage != null
                  ? Center(
                      child: InfoBar(
                        title: Text(_errorMessage!),
                        severity: InfoBarSeverity.error,
                      ),
                    )
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Thông tin đề thi
                        _buildExamInfo(),

                        const SizedBox(height: 24),

                        // Danh sách chủ đề
                        if (_examData != null) ...[
                          Text(
                            'Danh sách chủ đề',
                            style: FluentTheme.of(context).typography.subtitle,
                          ),
                          const SizedBox(height: 16),
                          _buildTagsList(),
                        ],

                        const SizedBox(height: 24),

                        // Danh sách câu hỏi
                        if (_examData != null) ...[
                          Text(
                            'Danh sách câu hỏi',
                            style: FluentTheme.of(context).typography.subtitle,
                          ),
                          const SizedBox(height: 16),
                          _buildQuestionsList(),
                        ],
                      ],
                    ),
        ),
      ),
    );
  }

  // Hiển thị thông tin đề thi
  Widget _buildExamInfo() {
    if (_examData == null) return const SizedBox.shrink();

    final subject = _examData!['subject'];
    final exam = _examData!['exam'];
    final difficultyRates = exam['difficulty_rates'];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${subject['name']} (${subject['code']})',
              style: FluentTheme.of(context).typography.title,
            ),
            const SizedBox(height: 16),
            Text(
              exam['name'],
              style: FluentTheme.of(context).typography.subtitle,
            ),
            const SizedBox(height: 8),
            Text('Thời gian: ${exam['duration']} phút'),
            Text('Tổng số câu hỏi: ${exam['total_questions']}'),
            Text('Số câu hỏi ngẫu nhiên: ${exam['random_questions_total']}'),
            if (exam['description'] != null) ...[
              const SizedBox(height: 8),
              Text('Mô tả: ${exam['description']}'),
            ],
            const SizedBox(height: 16),

            // Hiển thị tỷ lệ độ khó
            Text(
              'Tỷ lệ độ khó:',
              style: FluentTheme.of(context).typography.subtitle,
            ),
            const SizedBox(height: 8),
            _buildDifficultyInfo('Dễ', difficultyRates['easy']),
            _buildDifficultyInfo('Trung bình', difficultyRates['medium']),
            _buildDifficultyInfo('Khó', difficultyRates['hard']),
          ],
        ),
      ),
    );
  }

  Widget _buildDifficultyInfo(String level, Map<String, dynamic> data) {
    return Padding(
      padding: const EdgeInsets.only(left: 16, bottom: 8),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(level),
          ),
          Text('${data['percentage']}% (${data['questions']} câu)'),
          if (data['random_questions'] != null) ...[
            const Text(' - Chọn ngẫu nhiên: '),
            Text(
              '${data['random_questions']} câu',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTagsList() {
    final tags = _examData!['tags'] as List;
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: tags.length,
      itemBuilder: (context, index) {
        final tag = tags[index];
        final difficultyRates = tag['difficulty_rates'];

        return Card(
          padding: const EdgeInsets.all(16),
          margin: const EdgeInsets.only(bottom: 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                tag['name'],
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text('Tổng số câu hỏi: ${tag['num_questions']}'),
              const SizedBox(height: 8),
              _buildDifficultyInfo('Dễ', difficultyRates['easy']),
              _buildDifficultyInfo('Trung bình', difficultyRates['medium']),
              _buildDifficultyInfo('Khó', difficultyRates['hard']),
            ],
          ),
        );
      },
    );
  }

  Widget _buildQuestionsList() {
    final questions = _examData!['questions'] as List;
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: questions.length,
      itemBuilder: (context, index) {
        final question = questions[index];
        final answers = question['answers'] as List;
        final tags = question['tags'] as List;

        return Card(
          padding: const EdgeInsets.all(16),
          margin: const EdgeInsets.only(bottom: 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.grey.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      'Câu ${index + 1}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getDifficultyColor(question['type']),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      question['type'].toUpperCase(),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Chủ đề: ${tags.join(", ")}',
                    style: const TextStyle(color: Colors.grey),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(question['content']),
              const SizedBox(height: 8),
              ...answers.map((answer) {
                final isCorrect = answer['is_correct'] as bool;
                return Padding(
                  padding: const EdgeInsets.only(left: 16, bottom: 4),
                  child: Row(
                    children: [
                      Icon(
                        isCorrect
                            ? FluentIcons.check_mark
                            : FluentIcons.circle_ring,
                        color: isCorrect ? Colors.green : Colors.grey,
                        size: 16,
                      ),
                      const SizedBox(width: 8),
                      Expanded(child: Text(answer['content'])),
                    ],
                  ),
                );
              }).toList(),
            ],
          ),
        );
      },
    );
  }

  Color _getDifficultyColor(String type) {
    switch (type.toLowerCase()) {
      case 'easy':
        return Colors.green;
      case 'medium':
        return Colors.orange;
      case 'hard':
        return Colors.errorPrimaryColor;
      default:
        return Colors.grey;
    }
  }
}
