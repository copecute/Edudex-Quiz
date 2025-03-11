import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../../providers/exam_provider.dart';
import '../../providers/student_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../../services/exam_database_service.dart';
import 'package:sqflite/sqflite.dart';

class QuizManagementPage extends StatefulWidget {
  const QuizManagementPage({super.key});

  @override
  State<QuizManagementPage> createState() => _QuizManagementPageState();
}

class _QuizManagementPageState extends State<QuizManagementPage> {
  final _examIdController = TextEditingController();
  final _examDb = ExamDatabaseService();
  Map<String, dynamic>? _examData;
  String? _errorMessage;
  bool _isLoading = true;
  bool _showQuestions = false;
  Map<String, List<String>> _examErrors = {};
  final _searchController = TextEditingController();
  List<String> _selectedTags = [];
  String? _selectedDifficulty;
  List<dynamic> _filteredQuestions = [];

  @override
  void initState() {
    super.initState();
    _loadExamData();
    _searchController.addListener(_filterQuestions);
  }

  Future<void> _loadExamData() async {
    try {
      final data = await _examDb.getExamData();
      setState(() {
        _examData = data;
        _errorMessage = null;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Lỗi khi tải dữ liệu: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  // Hàm kiểm tra đề thi
  Future<void> _checkExam() async {
    final db = await _examDb.database;
    _examErrors.clear();

    try {
      // 1. Kiểm tra số lượng câu hỏi
      final examQuery = await db.query('exams', limit: 1);
      if (examQuery.isEmpty) throw Exception('Không tìm thấy thông tin đề thi');
      final exam = examQuery.first;
      final totalQuestions = exam['total_questions'] as int;

      final questionCount = Sqflite.firstIntValue(
          await db.rawQuery('SELECT COUNT(*) FROM questions'));

      if (questionCount! < totalQuestions) {
        _examErrors['total'] = [
          'Thiếu ${totalQuestions - questionCount} câu hỏi'
        ];
      }

      // 2. Kiểm tra câu hỏi trùng lặp
      final duplicateQuestions = await db.rawQuery('''
        SELECT content, COUNT(*) as count 
        FROM questions 
        GROUP BY content 
        HAVING count > 1
      ''');

      if (duplicateQuestions.isNotEmpty) {
        _examErrors['duplicate'] = duplicateQuestions
            .map((q) => 'Câu hỏi "${q['content']}" xuất hiện ${q['count']} lần')
            .toList();
      }

      // 3. Kiểm tra số lượng câu hỏi theo tag và độ khó
      final tagRates = await db.query('tag_difficulty_rates');
      final List<String> tagErrors = [];

      for (final rate in tagRates) {
        final tagId = rate['tag_id'];
        final difficulty = rate['difficulty'].toString();
        final requiredCount = rate['questions'] as int;

        final actualCount = Sqflite.firstIntValue(await db.rawQuery('''
          SELECT COUNT(*) FROM questions q
          JOIN question_tags qt ON q.id = qt.question_id
          JOIN tags t ON qt.tag_name = t.name
          WHERE t.id = ? AND q.type = ?
        ''', [tagId, difficulty]));

        if (actualCount! < requiredCount) {
          final tag = (await db.query(
            'tags',
            where: 'id = ?',
            whereArgs: [tagId],
            limit: 1,
          ))
              .first;

          tagErrors.add(
              'Tag "${tag['name']}" - ${difficulty}: còn thiếu ${requiredCount - actualCount} câu');
        }
      }

      if (tagErrors.isNotEmpty) {
        _examErrors['tags'] = tagErrors;
      }

      // Kiểm tra số câu hỏi thừa theo tag và độ khó
      final List<String> tagWarnings = [];
      final Map<String, int> excessByDifficulty =
          {}; // Lưu số câu thừa theo độ khó

      for (final rate in tagRates) {
        final tagId = rate['tag_id'];
        final difficulty = rate['difficulty'].toString();
        final requiredCount = rate['questions'] as int;

        final actualCount = Sqflite.firstIntValue(await db.rawQuery('''
          SELECT COUNT(*) FROM questions q
          JOIN question_tags qt ON q.id = qt.question_id
          JOIN tags t ON qt.tag_name = t.name
          WHERE t.id = ? AND q.type = ?
        ''', [tagId, difficulty]));

        if (actualCount! > requiredCount) {
          final tag = (await db.query(
            'tags',
            where: 'id = ?',
            whereArgs: [tagId],
            limit: 1,
          ))
              .first;

          final excess = actualCount - requiredCount;
          excessByDifficulty[difficulty] =
              (excessByDifficulty[difficulty] ?? 0) + excess;

          tagWarnings.add(
              'Tag "${tag['name']}" - ${difficulty}: thừa $excess câu ($actualCount/$requiredCount câu)');
        }
      }

      // Kiểm tra số câu random theo độ khó
      final examDifficultyRates = await db.query('exam_difficulty_rates');
      for (final rate in examDifficultyRates) {
        final difficulty = rate['difficulty'].toString();
        final randomQuestions = rate['random_questions'] as int;

        if (excessByDifficulty.containsKey(difficulty)) {
          final excess = excessByDifficulty[difficulty]!;
          final remainingExcess = excess - randomQuestions;

          if (remainingExcess > 0) {
            tagWarnings.add(
                '${difficulty}: Vẫn thừa $remainingExcess câu sau khi trừ ${randomQuestions} câu random');
          }
        }
      }

      if (tagWarnings.isNotEmpty) {
        _examErrors['tag_warnings'] = tagWarnings;
      }

      setState(() {});
    } catch (e) {
      print('❌ Lỗi kiểm tra đề thi: $e');
      _examErrors['error'] = ['Lỗi kiểm tra đề thi: $e'];
    }
  }

  void _filterQuestions() {
    if (_examData == null) return;

    final questions = _examData!['questions'] as List;
    final searchTerm = _searchController.text.toLowerCase();

    setState(() {
      _filteredQuestions = questions.where((question) {
        bool matchesSearch = searchTerm.isEmpty ||
            question['content'].toString().toLowerCase().contains(searchTerm);

        bool matchesTags = _selectedTags.isEmpty ||
            (question['tags'] as List)
                .any((tag) => _selectedTags.contains(tag.toString()));

        bool matchesDifficulty = _selectedDifficulty == null ||
            question['type'].toString().toLowerCase() ==
                _selectedDifficulty!.toLowerCase();

        return matchesSearch && matchesTags && matchesDifficulty;
      }).toList();
    });
  }

  @override
  void dispose() {
    _examIdController.dispose();
    _searchController.removeListener(_filterQuestions);
    _searchController.dispose();
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

                        // Card kiểm tra đề thi
                        _buildExamCheck(),

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
    if (_examData == null) return const SizedBox.shrink();

    final questions = _examData!['questions'] as List;
    final allTags = _getAllTags();

    // Khởi tạo _filteredQuestions nếu chưa có
    if (_filteredQuestions.isEmpty && !_searchController.text.isNotEmpty) {
      _filteredQuestions = List.from(questions);
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header với nút ẩn/hiện
        Row(
          children: [
            GestureDetector(
              onTap: () => setState(() => _showQuestions = !_showQuestions),
              child: Row(
                children: [
                  Text(
                    'Danh sách câu hỏi (${_filteredQuestions.length}/${questions.length})',
                    style: FluentTheme.of(context).typography.subtitle,
                  ),
                  const SizedBox(width: 8),
                  Icon(_showQuestions
                      ? FluentIcons.chevron_up
                      : FluentIcons.chevron_down),
                ],
              ),
            ),
          ],
        ),

        if (_showQuestions) ...[
          const SizedBox(height: 16),

          // Thanh tìm kiếm và bộ lọc
          Row(
            children: [
              // Ô tìm kiếm
              Expanded(
                child: TextBox(
                  controller: _searchController,
                  placeholder: 'Tìm kiếm câu hỏi...',
                  prefix: const Padding(
                    padding: EdgeInsets.all(8.0),
                    child: Icon(FluentIcons.search),
                  ),
                ),
              ),
              const SizedBox(width: 8),

              // Dropdown chọn độ khó
              ComboBox<String>(
                value: _selectedDifficulty,
                items: [
                  const ComboBoxItem(
                    value: 'Tất cả độ khó',
                    child: Text('Tất cả độ khó'),
                  ),
                  ...['Easy', 'Medium', 'Hard'].map((type) => ComboBoxItem(
                        value: type,
                        child: Text(type),
                      )),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedDifficulty = value;
                    _filterQuestions();
                  });
                },
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Chọn chủ đề
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: allTags.map((tag) {
              final isSelected = _selectedTags.contains(tag);
              return ToggleButton(
                checked: isSelected,
                onChanged: (value) {
                  setState(() {
                    if (value) {
                      _selectedTags.add(tag);
                    } else {
                      _selectedTags.remove(tag);
                    }
                    _filterQuestions();
                  });
                },
                child: Text(tag),
              );
            }).toList(),
          ),
          const SizedBox(height: 16),

          // Danh sách câu hỏi đã lọc
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _filteredQuestions.length,
            itemBuilder: (context, index) {
              final question = _filteredQuestions[index];
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
          ),
        ],
      ],
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

  // Widget hiển thị kết quả kiểm tra
  Widget _buildExamCheck() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(FluentIcons.diagnostic),
                const SizedBox(width: 8),
                Text(
                  'Kiểm tra đề thi',
                  style: FluentTheme.of(context).typography.subtitle,
                ),
                const Spacer(),
                FilledButton(
                  child: const Text('Kiểm tra'),
                  onPressed: _checkExam,
                ),
              ],
            ),
            if (_examErrors.isNotEmpty) ...[
              const SizedBox(height: 16),
              for (final entry in _examErrors.entries) ...[
                InfoBar(
                  title: Text(_getErrorTitle(entry.key)),
                  content: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children:
                        entry.value.map((error) => Text('• $error')).toList(),
                  ),
                  severity: entry.key == 'tag_warnings'
                      ? InfoBarSeverity.warning
                      : InfoBarSeverity.error,
                ),
                const SizedBox(height: 8),
              ],
            ],
          ],
        ),
      ),
    );
  }

  String _getErrorTitle(String key) {
    switch (key) {
      case 'total':
        return 'Số lượng câu hỏi không đủ';
      case 'duplicate':
        return 'Phát hiện câu hỏi trùng lặp';
      case 'tags':
        return 'Thiếu câu hỏi theo chủ đề/độ khó';
      case 'tag_warnings':
        return 'Thừa câu hỏi theo chủ đề/độ khó';
      default:
        return 'Lỗi kiểm tra';
    }
  }

  // Hàm lấy tất cả các tag có trong đề thi
  List<String> _getAllTags() {
    if (_examData == null) return [];

    final Set<String> tags = {};
    final questions = _examData!['questions'] as List;

    for (final question in questions) {
      tags.addAll((question['tags'] as List).map((t) => t.toString()));
    }

    return tags.toList()..sort();
  }
}
