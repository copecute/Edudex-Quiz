import 'package:fluent_ui/fluent_ui.dart';
import '../../services/exam_database_service.dart';
import 'package:intl/intl.dart';

class ResultsPage extends StatefulWidget {
  const ResultsPage({super.key});

  @override
  State<ResultsPage> createState() => _ResultsPageState();
}

enum SortField { time, score }

enum SortOrder { ascending, descending }

class _ResultsPageState extends State<ResultsPage> {
  final _examDb = ExamDatabaseService();
  final _searchController = TextEditingController();
  List<Map<String, dynamic>> _results = [];
  List<Map<String, dynamic>> _filteredResults = [];
  bool _isLoading = true;
  String? _errorMessage;
  SortField _sortField = SortField.time;
  SortOrder _sortOrder = SortOrder.descending;

  @override
  void initState() {
    super.initState();
    _loadResults();
    _searchController.addListener(_filterResults);
  }

  @override
  void dispose() {
    _searchController.removeListener(_filterResults);
    _searchController.dispose();
    super.dispose();
  }

  void _filterResults() {
    final searchTerm = _searchController.text.toLowerCase().trim();

    setState(() {
      if (searchTerm.isEmpty) {
        _filteredResults = List.from(_results);
      } else {
        _filteredResults = _results.where((result) {
          final studentCode = (result['student_code'] ?? '').toLowerCase();
          final examCode = (result['exam_code'] ?? '').toLowerCase();
          final fullName = (result['full_name'] ?? '').toLowerCase();

          return studentCode.contains(searchTerm) ||
              examCode.contains(searchTerm) ||
              fullName.contains(searchTerm);
        }).toList();
      }

      _sortResults();
    });
  }

  void _sortResults() {
    _filteredResults.sort((a, b) {
      if (_sortField == SortField.time) {
        final aTime = a['submitted_at'] != null
            ? DateTime.parse(a['submitted_at'])
            : DateTime(1970);
        final bTime = b['submitted_at'] != null
            ? DateTime.parse(b['submitted_at'])
            : DateTime(1970);

        return _sortOrder == SortOrder.ascending
            ? aTime.compareTo(bTime)
            : bTime.compareTo(aTime);
      } else {
        final aScore = a['score'] ?? 0.0;
        final bScore = b['score'] ?? 0.0;

        return _sortOrder == SortOrder.ascending
            ? aScore.compareTo(bScore)
            : bScore.compareTo(aScore);
      }
    });
  }

  Future<void> _loadResults() async {
    try {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      final results = await _examDb.getExamResults();
      setState(() {
        _results = results;
        _filteredResults = List.from(results);
        _sortResults();
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Lỗi khi tải kết quả: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: PageHeader(
        title: const Text('Kết quả bài thi'),
        commandBar: CommandBar(
          mainAxisAlignment: MainAxisAlignment.end,
          primaryItems: [
            CommandBarButton(
              icon: const Icon(FluentIcons.refresh),
              label: const Text('Làm mới'),
              onPressed: _isLoading ? null : _loadResults,
            ),
          ],
        ),
      ),
      content: Padding(
        padding: const EdgeInsets.all(16.0),
        child: _isLoading
            ? const Center(child: ProgressRing())
            : _errorMessage != null
                ? Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      InfoBar(
                        title: Text(_errorMessage!),
                        severity: InfoBarSeverity.error,
                        isLong: true,
                      ),
                      const SizedBox(height: 16),
                      FilledButton(
                        child: const Text('Thử lại'),
                        onPressed: _loadResults,
                      ),
                    ],
                  )
                : _results.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Text(
                              'Chưa có kết quả bài thi nào',
                              style: TextStyle(fontSize: 16),
                            ),
                            const SizedBox(height: 16),
                            FilledButton(
                              child: const Text('Làm mới'),
                              onPressed: _loadResults,
                            ),
                          ],
                        ),
                      )
                    : Card(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Padding(
                              padding: const EdgeInsets.all(16.0),
                              child: Row(
                                children: [
                                  Text(
                                    'Danh sách kết quả (${_filteredResults.length}/${_results.length})',
                                    style: const TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const Spacer(),
                                  // Tìm kiếm
                                  SizedBox(
                                    width: 300,
                                    child: TextBox(
                                      controller: _searchController,
                                      placeholder:
                                          'Tìm theo tên, mã SV, số báo danh...',
                                      prefix: const Padding(
                                        padding: EdgeInsets.only(left: 8.0),
                                        child: Icon(FluentIcons.search),
                                      ),
                                      suffix: _searchController.text.isNotEmpty
                                          ? IconButton(
                                              icon:
                                                  const Icon(FluentIcons.clear),
                                              onPressed: () {
                                                _searchController.clear();
                                              },
                                            )
                                          : null,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  // Sắp xếp
                                  ComboBox<SortField>(
                                    value: _sortField,
                                    items: [
                                      ComboBoxItem(
                                        value: SortField.time,
                                        child: const Text('Thời gian nộp'),
                                      ),
                                      ComboBoxItem(
                                        value: SortField.score,
                                        child: const Text('Điểm số'),
                                      ),
                                    ],
                                    onChanged: (value) {
                                      if (value != null) {
                                        setState(() {
                                          _sortField = value;
                                          _sortResults();
                                        });
                                      }
                                    },
                                  ),
                                  const SizedBox(width: 8),
                                  // Thứ tự sắp xếp
                                  IconButton(
                                    icon: Icon(_sortOrder == SortOrder.ascending
                                        ? FluentIcons.sort_up
                                        : FluentIcons.sort_down),
                                    onPressed: () {
                                      setState(() {
                                        _sortOrder =
                                            _sortOrder == SortOrder.ascending
                                                ? SortOrder.descending
                                                : SortOrder.ascending;
                                        _sortResults();
                                      });
                                    },
                                  ),
                                ],
                              ),
                            ),
                            const Divider(),
                            Expanded(
                              child: ListView(
                                children: [
                                  // Header
                                  Container(
                                    color: Colors.grey[20],
                                    child: Row(
                                      children: [
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 120,
                                            child: const Text(
                                              'Mã sinh viên',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 120,
                                            child: const Text(
                                              'Số báo danh',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 200,
                                            child: const Text(
                                              'Họ và tên',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 80,
                                            child: const Text(
                                              'Số câu đúng',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                              textAlign: TextAlign.center,
                                            ),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 80,
                                            child: const Text(
                                              'Điểm số',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                              textAlign: TextAlign.center,
                                            ),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 150,
                                            child: const Text(
                                              'Thời gian nộp',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                            ),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 80,
                                            child: const Text(
                                              'Thao tác',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                              textAlign: TextAlign.center,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  // Rows
                                  ..._filteredResults.map(
                                    (result) {
                                      final submittedAt =
                                          result['submitted_at'] != null
                                              ? DateFormat(
                                                      'dd/MM/yyyy HH:mm:ss')
                                                  .format(DateTime.parse(
                                                      result['submitted_at']))
                                              : 'N/A';

                                      return Container(
                                        decoration: BoxDecoration(
                                          border: Border(
                                            bottom: BorderSide(
                                              color: Colors.grey[30]!,
                                              width: 1,
                                            ),
                                          ),
                                        ),
                                        child: Row(
                                          children: [
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 120,
                                                child: Text(
                                                    result['student_code'] ??
                                                        'N/A'),
                                              ),
                                            ),
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 120,
                                                child: Text(
                                                    result['exam_code'] ??
                                                        'N/A'),
                                              ),
                                            ),
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 200,
                                                child: Text(
                                                    result['full_name'] ??
                                                        'N/A'),
                                              ),
                                            ),
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 80,
                                                child: Text(
                                                  '${result['correct_answers'] ?? 0}/${result['total_questions'] ?? 0}',
                                                  textAlign: TextAlign.center,
                                                ),
                                              ),
                                            ),
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 80,
                                                child: Text(
                                                  result['score'] != null
                                                      ? result['score']
                                                          .toStringAsFixed(1)
                                                      : 'N/A',
                                                  textAlign: TextAlign.center,
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    color: _getScoreColor(
                                                        result['score']),
                                                  ),
                                                ),
                                              ),
                                            ),
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 150,
                                                child: Text(submittedAt),
                                              ),
                                            ),
                                            Padding(
                                              padding:
                                                  const EdgeInsets.all(8.0),
                                              child: SizedBox(
                                                width: 80,
                                                child: Button(
                                                  child: const Icon(
                                                      FluentIcons.view,
                                                      size: 16),
                                                  onPressed: () {
                                                    _showResultDetails(result);
                                                  },
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                  ).toList(),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
      ),
    );
  }

  Color _getScoreColor(double? score) {
    if (score == null) return Colors.grey;
    if (score < 5.0) return Colors.red;
    if (score < 6.5) return Colors.orange;
    if (score < 8.0) return Colors.blue;
    return Colors.green;
  }

  void _showResultDetails(Map<String, dynamic> result) {
    showDialog(
      context: context,
      builder: (context) => ContentDialog(
        title: Row(
          children: [
            const Icon(FluentIcons.test_plan),
            const SizedBox(width: 8),
            Text('Chi tiết kết quả'),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildDetailRow('Mã sinh viên:', result['student_code']),
              _buildDetailRow('Số báo danh:', result['exam_code']),
              _buildDetailRow('Họ và tên:', result['full_name']),
              _buildDetailRow('Số câu đúng:',
                  '${result['correct_answers']}/${result['total_questions']}'),
              _buildDetailRow('Điểm số:', result['score']?.toStringAsFixed(1)),
              _buildDetailRow(
                  'Thời gian nộp:',
                  DateFormat('dd/MM/yyyy HH:mm:ss')
                      .format(DateTime.parse(result['submitted_at']))),
              const SizedBox(height: 16),
              if (result['log_content'] != null) ...[
                const Text('Nội dung bài làm:',
                    style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Container(
                  height: 300,
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[100]),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  padding: const EdgeInsets.all(8),
                  child: SingleChildScrollView(
                    child: SelectableText(
                        result['log_content'] ?? 'Không có nội dung'),
                  ),
                ),
              ],
            ],
          ),
        ),
        actions: [
          Button(
            child: const Text('Đóng'),
            onPressed: () => Navigator.pop(context),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String? value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label,
                style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
          Expanded(
            child: Text(value ?? 'N/A'),
          ),
        ],
      ),
    );
  }
}
