import 'package:fluent_ui/fluent_ui.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class StudentManagementPage extends StatefulWidget {
  const StudentManagementPage({super.key});

  @override
  State<StudentManagementPage> createState() => _StudentManagementPageState();
}

class _StudentManagementPageState extends State<StudentManagementPage> {
  List<dynamic> _students = [];
  String? _errorMessage;
  bool _isLoading = true;
  String? _serverUrl;

  @override
  void initState() {
    super.initState();
    _fetchStudents();
  }

  Future<void> _fetchStudents() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('user_token');
    final selectedShiftId = prefs.getInt('selected_shift_id');
    final selectedRoomId = prefs.getInt('selected_room_id');
    _serverUrl = prefs.getString('server_url');

    if (token == null ||
        selectedShiftId == null ||
        selectedRoomId == null ||
        _serverUrl == null) {
      setState(() {
        _errorMessage = 'Thông tin không đầy đủ để lấy danh sách sinh viên.';
        _isLoading = false;
      });
      return;
    }

    try {
      final url = Uri.parse(
          '$_serverUrl/api/exam-schedule/shifts/$selectedShiftId/rooms/$selectedRoomId/students');
      print('Đang lấy danh sách sinh viên: $url');
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
          _students = data['data'];
          _errorMessage = null;
        });
      } else {
        setState(() {
          _errorMessage =
              data['message'] ?? 'Không thể lấy danh sách sinh viên';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Lỗi khi lấy danh sách sinh viên: $e';
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
