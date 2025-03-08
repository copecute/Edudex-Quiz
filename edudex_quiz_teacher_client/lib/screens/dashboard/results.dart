import 'package:fluent_ui/fluent_ui.dart';
import '../../services/exam_database_service.dart';
import 'package:intl/intl.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class ResultsPage extends StatefulWidget {
  const ResultsPage({super.key});

  @override
  State<ResultsPage> createState() => _ResultsPageState();
}

enum SortField { time, score }

enum SortOrder { ascending, descending }

enum SubmissionStatus { pending, success, error }

class _ResultsPageState extends State<ResultsPage> {
  final _examDb = ExamDatabaseService();
  final _searchController = TextEditingController();
  List<Map<String, dynamic>> _results = [];
  List<Map<String, dynamic>> _filteredResults = [];
  bool _isLoading = true;
  String? _errorMessage;
  SortField _sortField = SortField.time;
  SortOrder _sortOrder = SortOrder.descending;
  final Map<int, SubmissionStatus> _submissionStatuses = {};
  final Map<int, String> _submissionErrors = {};
  String? _examPeriodId;
  String? _token;
  bool _isSubmitting = false;
  List<String> _submissionLogs = [];

  @override
  void initState() {
    super.initState();
    _loadResults();
    _searchController.addListener(_filterResults);
    _loadConfig();
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

  Future<void> _loadConfig() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _examPeriodId = prefs.getString('exam_period_id');
      _token = prefs.getString('user_token');
    });
  }

  Future<void> _submitResult(Map<String, dynamic> result) async {
    final resultId = result['id'] as int;
    try {
      final prefs = await SharedPreferences.getInstance();
      final serverUrl = prefs.getString('server_url');
      final db = await _examDb.database;

      // Lấy ID kỳ thi từ bảng test_sessions
      final testSession = await db.query(
        'test_sessions',
        columns: ['id'],
        limit: 1,
      );
      if (testSession.isEmpty) {
        throw Exception('Không tìm thấy thông tin kỳ thi');
      }
      _examPeriodId = testSession.first['id'].toString();

      // Lấy ID ca thi từ bảng shifts
      final shift = await db.query(
        'shifts',
        columns: ['id'],
        limit: 1, // Lấy ca thi đầu tiên
      );
      if (shift.isEmpty) {
        throw Exception('Không tìm thấy thông tin ca thi');
      }
      final shiftId = shift.first['id'].toString();

      // Lấy ID phòng thi từ bảng rooms
      final room = await db.query(
        'rooms',
        columns: ['id'],
        limit: 1, // Lấy phòng thi đầu tiên
      );
      if (room.isEmpty) {
        throw Exception('Không tìm thấy thông tin phòng thi');
      }
      final roomId = room.first['id'].toString();

      if (_token == null) {
        throw Exception('Thiếu token xác thực');
      }

      if (serverUrl == null) {
        throw Exception('Thiếu địa chỉ máy chủ');
      }

      setState(() {
        _submissionStatuses[resultId] = SubmissionStatus.pending;
        _submissionErrors.remove(resultId);
        _submissionLogs
            .add('Đang nộp kết quả của thí sinh ${result['student_code']}...');
      });

      final response = await http.post(
        Uri.parse('$serverUrl/api/exam-periods/$_examPeriodId/results'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'copecute $_token',
        },
        body: json.encode({
          'shift_id': shiftId,
          'room_code': roomId,
          'exam_code': result['exam_code'],
          'student_code': result['student_code'],
          'correct_answers': result['correct_answers'],
          'total_questions': result['total_questions'],
          'score': result['score'],
          'note': result['note'],
          'log_file': result['log_file'],
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _submissionStatuses[resultId] = SubmissionStatus.success;
          _submissionLogs.add(
              '✅ Đã nộp thành công kết quả của thí sinh ${result['student_code']}');
        });
      } else {
        throw Exception(data['message'] ?? 'Lỗi không xác định');
      }
    } catch (e) {
      print('❌ Lỗi khi nộp kết quả $resultId: $e');
      setState(() {
        _submissionStatuses[resultId] = SubmissionStatus.error;
        _submissionErrors[resultId] = e.toString();
        _submissionLogs.add(
            '❌ Lỗi khi nộp kết quả của thí sinh ${result['student_code']}: ${e.toString()}');
      });
    }
  }

  Future<void> _submitAllResults() async {
    setState(() {
      _isSubmitting = true;
    });

    try {
      for (final result in _filteredResults) {
        if (_submissionStatuses[result['id']] != SubmissionStatus.success) {
          await _submitResult(result);
        }
      }
    } finally {
      setState(() {
        _isSubmitting = false;
      });
    }
  }

  Widget _buildSubmissionStatus(Map<String, dynamic> result) {
    final status = _submissionStatuses[result['id']];
    final error = _submissionErrors[result['id']];

    if (status == null) {
      return const SizedBox(width: 24);
    }

    switch (status) {
      case SubmissionStatus.pending:
        return const SizedBox(
          width: 24,
          height: 24,
          child: ProgressRing(strokeWidth: 2),
        );
      case SubmissionStatus.success:
        return const Icon(
          FluentIcons.check_mark,
          color: Colors.successPrimaryColor,
          size: 20,
        );
      case SubmissionStatus.error:
        return Tooltip(
          message: error ?? 'Lỗi không xác định',
          child: IconButton(
            icon: const Icon(
              FluentIcons.error,
              color: Colors.errorPrimaryColor,
              size: 20,
            ),
            onPressed: () => _submitResult(result),
          ),
        );
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
            CommandBarButton(
              icon: const Icon(FluentIcons.upload),
              label: const Text('Nộp tất cả'),
              onPressed: _isSubmitting ? null : _submitAllResults,
            ),
          ],
        ),
      ),
      content: Column(
        children: [
          Expanded(
            child: Padding(
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
                                  const Icon(FluentIcons.info),
                                  const Text(
                                    'Chưa có kết quả bài thi nào',
                                    style: TextStyle(fontSize: 20),
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
                                              padding:
                                                  EdgeInsets.only(left: 8.0),
                                              child: Icon(FluentIcons.search),
                                            ),
                                            suffix: _searchController
                                                    .text.isNotEmpty
                                                ? IconButton(
                                                    icon: const Icon(
                                                        FluentIcons.clear),
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
                                              child:
                                                  const Text('Thời gian nộp'),
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
                                          icon: Icon(
                                              _sortOrder == SortOrder.ascending
                                                  ? FluentIcons.sort_up
                                                  : FluentIcons.sort_down),
                                          onPressed: () {
                                            setState(() {
                                              _sortOrder = _sortOrder ==
                                                      SortOrder.ascending
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
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 120,
                                                  child: const Text(
                                                    'Mã sinh viên',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 120,
                                                  child: const Text(
                                                    'Số báo danh',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 200,
                                                  child: const Text(
                                                    'Họ và tên',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 80,
                                                  child: const Text(
                                                    'Số câu đúng',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                    textAlign: TextAlign.center,
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 80,
                                                  child: const Text(
                                                    'Điểm số',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                    textAlign: TextAlign.center,
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 150,
                                                  child: const Text(
                                                    'Thời gian nộp',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 100,
                                                  child: const Text(
                                                    'Ghi chú',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 40,
                                                  child: const Text(
                                                    'TT',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                    textAlign: TextAlign.center,
                                                  ),
                                                ),
                                              ),
                                              Padding(
                                                padding:
                                                    const EdgeInsets.all(8.0),
                                                child: SizedBox(
                                                  width: 80,
                                                  child: const Text(
                                                    'Thao tác',
                                                    style: TextStyle(
                                                        fontWeight:
                                                            FontWeight.bold),
                                                    textAlign: TextAlign.center,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        // Rows
                                        ..._filteredResults
                                            .map((result) => Container(
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
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 120,
                                                          child: Text(result[
                                                                  'student_code'] ??
                                                              'N/A'),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 120,
                                                          child: Text(result[
                                                                  'exam_code'] ??
                                                              'N/A'),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 200,
                                                          child: Text(result[
                                                                  'full_name'] ??
                                                              'N/A'),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 80,
                                                          child: Text(
                                                            '${result['correct_answers'] ?? 0}/${result['total_questions'] ?? 0}',
                                                            textAlign: TextAlign
                                                                .center,
                                                          ),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 80,
                                                          child: Text(
                                                            result['score'] !=
                                                                    null
                                                                ? result[
                                                                        'score']
                                                                    .toStringAsFixed(
                                                                        1)
                                                                : 'N/A',
                                                            textAlign: TextAlign
                                                                .center,
                                                            style: TextStyle(
                                                              fontWeight:
                                                                  FontWeight
                                                                      .bold,
                                                              color: _getScoreColor(
                                                                  result[
                                                                      'score']),
                                                            ),
                                                          ),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 150,
                                                          child: Text(
                                                            DateFormat(
                                                                    'dd/MM/yyyy HH:mm:ss')
                                                                .format(DateTime
                                                                    .parse(result[
                                                                        'submitted_at'])),
                                                          ),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 100,
                                                          child: Row(
                                                            children: [
                                                              Expanded(
                                                                child: Text(
                                                                  result['note'] ??
                                                                      '',
                                                                  overflow:
                                                                      TextOverflow
                                                                          .ellipsis,
                                                                  maxLines: 1,
                                                                ),
                                                              ),
                                                              IconButton(
                                                                icon: const Icon(
                                                                    FluentIcons
                                                                        .edit),
                                                                onPressed: () =>
                                                                    _showNoteDialog(
                                                                        result),
                                                              ),
                                                            ],
                                                          ),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 40,
                                                          child:
                                                              _buildSubmissionStatus(
                                                                  result),
                                                        ),
                                                      ),
                                                      Padding(
                                                        padding:
                                                            const EdgeInsets
                                                                .all(8.0),
                                                        child: SizedBox(
                                                          width: 80,
                                                          child: Button(
                                                            child: const Icon(
                                                                FluentIcons
                                                                    .view,
                                                                size: 16),
                                                            onPressed: () {
                                                              _showResultDetails(
                                                                  result);
                                                            },
                                                          ),
                                                        ),
                                                      ),
                                                    ],
                                                  ),
                                                ))
                                            .toList(),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
            ),
          ),
        ],
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
            const Text('Chi tiết kết quả'),
            const Spacer(),
            IconButton(
              icon: const Icon(FluentIcons.edit),
              onPressed: () {
                Navigator.pop(context);
                _showNoteDialog(result);
              },
            ),
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
              if (result['note']?.isNotEmpty == true) ...[
                const SizedBox(height: 16),
                const Text('Ghi chú:',
                    style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[100]),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(result['note']),
                ),
              ],
              if (result['log_content'] != null) ...[
                const SizedBox(height: 16),
                const Text('Chi tiết bài làm:',
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

  void _showNoteDialog(Map<String, dynamic> result) {
    final noteController = TextEditingController(text: result['note'] ?? '');

    showDialog(
      context: context,
      builder: (context) => ContentDialog(
        title: Row(
          children: [
            const Icon(FluentIcons.edit_note),
            const SizedBox(width: 8),
            const Text('Ghi chú'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
                'Thí sinh: ${result['full_name'] ?? result['student_code'] ?? 'N/A'}'),
            const SizedBox(height: 8),
            TextBox(
              controller: noteController,
              maxLines: 5,
              placeholder: 'Nhập ghi chú...',
            ),
          ],
        ),
        actions: [
          Button(
            child: const Text('Hủy'),
            onPressed: () => Navigator.pop(context),
          ),
          FilledButton(
            child: const Text('Lưu'),
            onPressed: () async {
              try {
                // Cập nhật ghi chú vào database
                final db = await _examDb.database;
                await db.update(
                  'exam_results',
                  {'note': noteController.text},
                  where: 'id = ?',
                  whereArgs: [result['id']],
                );

                // Cập nhật state
                setState(() {
                  result['note'] = noteController.text;
                });

                if (mounted) {
                  Navigator.pop(context);
                }
              } catch (e) {
                print('❌ Lỗi khi lưu ghi chú: $e');
                if (mounted) {
                  showDialog(
                    context: context,
                    builder: (context) => ContentDialog(
                      title: const Text('Lỗi'),
                      content: Text('Không thể lưu ghi chú: ${e.toString()}'),
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
