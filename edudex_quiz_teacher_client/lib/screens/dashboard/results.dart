import 'package:fluent_ui/fluent_ui.dart';
import 'package:intl/intl.dart';
import '../../services/database_service.dart';
import '../../services/exam_database_service.dart';
import '../../utils/crypto.dart';
import 'dart:convert';

class ResultsPage extends StatefulWidget {
  const ResultsPage({super.key});

  @override
  State<ResultsPage> createState() => _ResultsPageState();
}

class _ResultsPageState extends State<ResultsPage> {
  final _examDb = ExamDatabaseService();
  final _db = DatabaseService();
  List<Map<String, dynamic>> _examResults = [];
  Map<String, dynamic>? _selectedResult;
  String? _decodedContent;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadExamResults();
  }

  Future<void> _loadExamResults() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // Lấy danh sách kết quả bài thi từ SQLite
      final results = await _db.getExamResults();

      setState(() {
        _examResults = results;
        _isLoading = false;
      });
    } catch (e) {
      print('❌ Lỗi khi tải kết quả bài thi: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _viewResultDetails(Map<String, dynamic> result) {
    try {
      // Giải mã nội dung file từ base64
      final decodedContent =
          AppCrypto.decryptFromBase64(result['submission_file']);

      setState(() {
        _selectedResult = result;
        _decodedContent = decodedContent;
      });
    } catch (e) {
      print('❌ Lỗi khi giải mã kết quả: $e');
      showDialog(
        context: context,
        builder: (context) => ContentDialog(
          title: const Text('Lỗi'),
          content: Text('Không thể đọc nội dung bài thi: ${e.toString()}'),
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

  String _formatDateTime(String? dateTimeStr) {
    if (dateTimeStr == null) return 'N/A';
    try {
      final dateTime = DateTime.parse(dateTimeStr);
      return DateFormat('dd/MM/yyyy HH:mm:ss').format(dateTime);
    } catch (e) {
      return dateTimeStr;
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
              onPressed: _loadExamResults,
            ),
          ],
        ),
      ),
      content: _isLoading
          ? const Center(child: ProgressRing())
          : Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Danh sách kết quả
                SizedBox(
                  width: 350,
                  child: Card(
                    padding: const EdgeInsets.all(8),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Padding(
                          padding: EdgeInsets.all(8.0),
                          child: Text(
                            'Danh sách bài thi',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Expanded(
                          child: _examResults.isEmpty
                              ? const Center(
                                  child: Text('Chưa có kết quả bài thi nào'),
                                )
                              : ListView.builder(
                                  itemCount: _examResults.length,
                                  itemBuilder: (context, index) {
                                    final result = _examResults[index];
                                    final isSelected = _selectedResult !=
                                            null &&
                                        _selectedResult!['id'] == result['id'];

                                    return HoverButton(
                                      onPressed: () =>
                                          _viewResultDetails(result),
                                      builder: (context, states) {
                                        return Container(
                                          color: isSelected
                                              ? Colors.blue.lightest
                                              : null,
                                          padding: const EdgeInsets.all(8),
                                          child: Row(
                                            children: [
                                              Expanded(
                                                child: Column(
                                                  crossAxisAlignment:
                                                      CrossAxisAlignment.start,
                                                  children: [
                                                    Text(
                                                      '${result['student_name'] ?? 'Không có tên'} (${result['student_code'] ?? 'N/A'})',
                                                    ),
                                                    Text(
                                                      'Nộp lúc: ${_formatDateTime(result['submitted_at'])}',
                                                      style: TextStyle(
                                                          fontSize: 12),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                              Text(
                                                'Điểm: ${result['score'] ?? 'N/A'}',
                                                style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  color:
                                                      (result['score'] ?? 0) >=
                                                              5
                                                          ? Colors.green
                                                          : Colors.red,
                                                ),
                                              ),
                                            ],
                                          ),
                                        );
                                      },
                                    );
                                  },
                                ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                // Chi tiết kết quả
                Expanded(
                  child: Card(
                    padding: const EdgeInsets.all(16),
                    child: _selectedResult == null
                        ? const Center(
                            child: Text('Chọn một bài thi để xem chi tiết'),
                          )
                        : Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Chi tiết bài thi: ${_selectedResult!['student_name'] ?? 'Không có tên'}',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Expanded(
                                child: SingleChildScrollView(
                                  child: SelectableText(
                                    _decodedContent ?? 'Không có nội dung',
                                    style: const TextStyle(
                                      fontFamily:
                                          'Consolas, Courier New, monospace',
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
              ],
            ),
    );
  }
}
