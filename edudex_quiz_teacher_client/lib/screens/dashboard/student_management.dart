import 'package:fluent_ui/fluent_ui.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/exam_database_service.dart';

class StudentManagementPage extends StatefulWidget {
  const StudentManagementPage({super.key});

  @override
  State<StudentManagementPage> createState() => _StudentManagementPageState();
}

class _StudentManagementPageState extends State<StudentManagementPage> {
  final _examDb = ExamDatabaseService();
  List<Map<String, dynamic>> _students = [];
  String? _errorMessage;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadStudents();
  }

  Future<void> _loadStudents() async {
    try {
      final students = await _examDb.getStudents();
      setState(() {
        _students = students;
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

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      // header: const PageHeader(
      //   title: Text('Danh sách thí sinh'),
      // ),
      content: Padding(
        padding: const EdgeInsets.only(left: 20.0, right: 20.0, bottom: 20.0),
        child: _isLoading
            ? const Center(child: ProgressRing())
            : _errorMessage != null
                ? InfoBar(
                    title: Text(_errorMessage!),
                    severity: InfoBarSeverity.warning,
                  )
                : Card(
                    child: ListView(
                      children: [
                        // Header
                        Padding(
                          padding: const EdgeInsets.all(12.0),
                          child: Row(
                            children: [
                              const Icon(FluentIcons.people),
                              const SizedBox(width: 8),
                              Text(
                                'Danh sách thí sinh (${_students.length})',
                                style:
                                    FluentTheme.of(context).typography.subtitle,
                              ),
                            ],
                          ),
                        ),
                        const Divider(),

                        // Table header
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12.0),
                          child: Row(
                            children: const [
                              SizedBox(width: 50, child: Text('STT')),
                              SizedBox(width: 100, child: Text('Số báo danh')),
                              SizedBox(width: 100, child: Text('Mã SV')),
                              Expanded(flex: 2, child: Text('Họ và tên')),
                              SizedBox(width: 100, child: Text('Ngày sinh')),
                              SizedBox(width: 80, child: Text('Giới tính')),
                              Expanded(child: Text('Số điện thoại')),
                              SizedBox(width: 80, child: Text('Số ghế')),
                            ],
                          ),
                        ),
                        const Divider(),

                        // Table content
                        ListView.builder(
                          shrinkWrap: true,
                          itemCount: _students.length,
                          itemBuilder: (context, index) {
                            final student = _students[index];
                            return HoverButton(
                              onPressed: () {
                                // Show student details in a dialog
                                showDialog(
                                  context: context,
                                  builder: (context) =>
                                      _buildStudentDetailsDialog(student),
                                );
                              },
                              builder: (context, states) {
                                return Container(
                                  color: states.isHovering
                                      ? FluentTheme.of(context)
                                          .resources
                                          .subtleFillColorSecondary
                                      : Colors.transparent,
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 12.0, vertical: 8.0),
                                    child: Row(
                                      children: [
                                        SizedBox(
                                            width: 50,
                                            child:
                                                Text((index + 1).toString())),
                                        SizedBox(
                                            width: 100,
                                            child: Text(
                                                student['exam_code'] ?? '-')),
                                        SizedBox(
                                            width: 100,
                                            child: Text(
                                                student['student_code'] ??
                                                    '-')),
                                        Expanded(
                                            flex: 2,
                                            child: Text(
                                                student['full_name'] ?? '-')),
                                        SizedBox(
                                            width: 100,
                                            child: Text(
                                                student['date_of_birth'] ??
                                                    '-')),
                                        SizedBox(
                                            width: 80,
                                            child:
                                                Text(student['gender'] ?? '-')),
                                        Expanded(
                                            child:
                                                Text(student['phone'] ?? '-')),
                                        SizedBox(
                                            width: 80,
                                            child: Text(student['seat_number']
                                                    ?.toString() ??
                                                '-')),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            );
                          },
                        ),
                      ],
                    ),
                  ),
      ),
    );
  }

  Widget _buildStudentDetailsDialog(Map<String, dynamic> student) {
    return ContentDialog(
      title: Row(
        children: [
          const Icon(FluentIcons.contact_info),
          const SizedBox(width: 8),
          Text('Thông tin thí sinh'),
        ],
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildDetailRow('Số báo danh:', student['exam_code']),
          _buildDetailRow('Mã sinh viên:', student['student_code']),
          _buildDetailRow('Họ và tên:', student['full_name']),
          _buildDetailRow('Ngày sinh:', student['date_of_birth']),
          _buildDetailRow('Giới tính:', student['gender']),
          _buildDetailRow('Số điện thoại:', student['phone']),
          _buildDetailRow('Số ghế:', student['seat_number']?.toString()),
          const SizedBox(height: 8),
          InfoLabel(
            label: 'Địa chỉ:',
            child: Text(student['address'] ?? 'N/A'),
          ),
        ],
      ),
      actions: [
        Button(
          child: const Text('Đóng'),
          onPressed: () => Navigator.pop(context),
        ),
      ],
    );
  }

  Widget _buildDetailRow(String label, String? value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label),
          ),
          Expanded(
            child: Text(value ?? 'N/A'),
          ),
        ],
      ),
    );
  }
}
