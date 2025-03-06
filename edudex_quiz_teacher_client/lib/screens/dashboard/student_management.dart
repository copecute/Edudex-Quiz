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
      final response = await http.get(
        Uri.parse(
            '$_serverUrl/api/exam-schedule/shifts/$selectedShiftId/rooms/$selectedRoomId/students'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      final data = json.decode(response.body);

      if (data['success'] == true) {
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
      header: const PageHeader(
        title: Text('Quản lý thí sinh'),
      ),
      content: _isLoading
          ? const Center(child: ProgressRing())
          : _errorMessage != null
              ? InfoBar(
                  title: Text(_errorMessage!),
                  severity: InfoBarSeverity.warning,
                )
              : ListView.builder(
                  itemCount: _students.length,
                  itemBuilder: (context, index) {
                    final student = _students[index];
                    return ListTile(
                      title: Text(student['full_name']),
                      subtitle:
                          Text('Mã sinh viên: ${student['student_code']}'),
                      trailing: Text('Số ghế: ${student['seat_number']}'),
                    );
                  },
                ),
    );
  }
}
