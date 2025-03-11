import 'package:fluent_ui/fluent_ui.dart';
import '../models/exam_schedule.dart';
import '../services/exam_database_service.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../screens/dashboard/dashboard_screen.dart';

class ExamPeriodSelector extends StatefulWidget {
  final List<ExamPeriod> examPeriods;
  final Function(ExamPeriod, ExamShift, ExamRoom) onSelected;
  final Function(int) onError;

  const ExamPeriodSelector({
    super.key,
    required this.examPeriods,
    required this.onSelected,
    required this.onError,
  });

  @override
  State<ExamPeriodSelector> createState() => _ExamPeriodSelectorState();
}

class _ExamPeriodSelectorState extends State<ExamPeriodSelector> {
  final _examDb = ExamDatabaseService();
  bool _isLoading = false;
  ExamPeriod? _selectedPeriod;
  ExamShift? _selectedShift;
  ExamRoom? _selectedRoom;

  @override
  void initState() {
    super.initState();
    if (widget.examPeriods.length == 1) {
      _selectedPeriod = widget.examPeriods.first;
      if (_selectedPeriod!.shifts.length == 1) {
        _selectedShift = _selectedPeriod!.shifts.first;
        if (_selectedShift!.rooms.length == 1) {
          _selectedRoom = _selectedShift!.rooms.first;
          // Tự động tải dữ liệu nếu chỉ có 1 kỳ thi, 1 ca thi và 1 phòng thi
          WidgetsBinding.instance.addPostFrameCallback((_) async {
            try {
              await _loadAndSaveData(
                _selectedPeriod!,
                _selectedShift!,
                _selectedRoom!,
              );
              if (mounted) {
                widget.onSelected(
                    _selectedPeriod!, _selectedShift!, _selectedRoom!);
                Navigator.pop(context);
              }
            } catch (e) {
              // Nếu có lỗi, vẫn giữ dialog để người dùng có thể thấy thông báo lỗi
              print('❌ Lỗi khi tải dữ liệu tự động: $e');
            }
          });
        }
      }
    }
  }

  String _formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _onPeriodSelected() async {
    try {
      final period = _selectedPeriod;
      final shift = _selectedShift;
      final room = _selectedRoom;

      if (period != null && shift != null && room != null) {
        // Lưu thông tin vào database
        final examDb = ExamDatabaseService();
        await examDb.saveSessionInfo(period, shift, room);

        // Chỉ lưu room_capacity vào SharedPreferences vì cần cho việc kiểm tra kết nối máy
        final prefs = await SharedPreferences.getInstance();
        await prefs.setInt('room_capacity', room.capacity);
      } else {
        throw Exception('Vui lòng chọn đầy đủ thông tin');
      }
    } catch (e) {
      print('❌ Lỗi lưu session info: $e');
      rethrow;
    }
  }

  void _showErrorDialog(String title, String message) {
    if (!mounted) return;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      showDialog(
        context: context,
        builder: (context) => ContentDialog(
          title: Text(title),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  FluentIcons.warning,
                  color: Colors.warningPrimaryColor,
                  size: 24,
                ),
                const SizedBox(height: 8),
                Text(
                  message,
                  style: const TextStyle(height: 1.5),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Vui lòng liên hệ quản trị viên để được hỗ trợ.',
                  style: TextStyle(
                    fontStyle: FontStyle.italic,
                    color: Colors.warningPrimaryColor,
                  ),
                ),
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
    });
  }

  Future<void> _loadAndSaveData(
      ExamPeriod period, ExamShift shift, ExamRoom room) async {
    try {
      setState(() => _isLoading = true);
      await _onPeriodSelected();

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('user_token');
      final serverUrl = prefs.getString('server_url');

      // Tải dữ liệu
      final examResponse = await http.get(
        Uri.parse(
            '$serverUrl/api/exam-schedule/shifts/${shift.id}/rooms/${room.id}/exam'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      final studentsResponse = await http.get(
        Uri.parse(
            '$serverUrl/api/exam-schedule/shifts/${shift.id}/rooms/${room.id}/students'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      // Xử lý response
      final examData = json.decode(examResponse.body);
      final studentsData = json.decode(studentsResponse.body);

      // Kiểm tra response
      if (examResponse.statusCode == 200 &&
          studentsResponse.statusCode == 200) {
        // Kiểm tra status error từ server
        if (examData['status'] == 'error' ||
            studentsData['status'] == 'error') {
          widget.onError(1);
          _showErrorDialog(
              'Lỗi tải dữ liệu',
              examData['message'] ??
                  studentsData['message'] ??
                  'Không thể tải dữ liệu từ máy chủ');
          return;
        }

        try {
          // Lưu vào database
          await _examDb.updateExamData(examData['data']);
          await _examDb.updateStudents(studentsData['data']);

          if (mounted) {
            widget.onSelected(period, shift, room);
          }
        } catch (e) {
          print('❌ Lỗi lưu dữ liệu: $e');
          widget.onError(1);
          _showErrorDialog('Lỗi lưu dữ liệu', 'Không thể lưu dữ liệu: $e');
        }
      } else {
        // Xử lý lỗi HTTP status
        widget.onError(1);
        String message = '';

        if (examResponse.statusCode != 200) {
          try {
            final errorData = json.decode(examResponse.body);
            message =
                'Lỗi tải đề thi: ${errorData['message'] ?? 'Không thể tải đề thi'}';
          } catch (e) {
            message = 'Không thể tải đề thi';
          }
        }

        if (studentsResponse.statusCode != 200) {
          try {
            final errorData = json.decode(studentsResponse.body);
            message += message.isNotEmpty ? '\n\n' : '';
            message +=
                'Lỗi tải danh sách thí sinh: ${errorData['message'] ?? 'Không thể tải danh sách thí sinh'}';
          } catch (e) {
            message += message.isNotEmpty ? '\n\n' : '';
            message += 'Không thể tải danh sách thí sinh';
          }
        }

        _showErrorDialog('Lỗi tải dữ liệu', message);
      }
    } catch (e) {
      print('❌ Lỗi khi tải dữ liệu: $e');
      widget.onError(1);
      _showErrorDialog('Lỗi không mong muốn',
          'Đã xảy ra lỗi: $e\n\nVui lòng thử lại sau hoặc liên hệ hỗ trợ kỹ thuật.');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ContentDialog(
      title: Row(
        children: const [
          Icon(FluentIcons.calendar),
          SizedBox(width: 8),
          Text('Chọn ca thi'),
        ],
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ListView.separated(
            shrinkWrap: true,
            itemCount: widget.examPeriods.first.shifts.length,
            separatorBuilder: (context, index) => const SizedBox(height: 8),
            itemBuilder: (context, index) {
              final shift = widget.examPeriods.first.shifts[index];

              return Card(
                padding: const EdgeInsets.all(12),
                child: RadioButton(
                  checked: _selectedShift == shift,
                  onChanged: (value) {
                    setState(() {
                      _selectedShift = shift;
                      _selectedRoom =
                          shift.rooms.length == 1 ? shift.rooms.first : null;
                    });
                  },
                  content: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            FluentIcons.calendar_day,
                            size: 16,
                            color: FluentTheme.of(context).accentColor,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'Ngày ${shift.startTime.day}/${shift.startTime.month}/${shift.startTime.year}',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(FluentIcons.clock, size: 16),
                          const SizedBox(width: 8),
                          Text(
                            '${shift.startTime.hour}:${shift.startTime.minute.toString().padLeft(2, '0')} - '
                            '${shift.endTime.hour}:${shift.endTime.minute.toString().padLeft(2, '0')}',
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(FluentIcons.timer, size: 16),
                          const SizedBox(width: 8),
                          Text(
                            '${shift.endTime.difference(shift.startTime).inMinutes} phút',
                          ),
                        ],
                      ),
                      if (_selectedShift == shift &&
                          shift.rooms.length > 1) ...[
                        const SizedBox(height: 16),
                        const Text('Chọn phòng thi:',
                            style: TextStyle(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: shift.rooms.map((room) {
                            return ToggleButton(
                              checked: _selectedRoom == room,
                              onChanged: (value) {
                                setState(() {
                                  _selectedRoom = room;
                                });
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(8.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(room.name),
                                    Text(
                                      '${room.location} - ${room.subject.name}',
                                      style: FluentTheme.of(context)
                                          .typography
                                          .caption,
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ] else
                        for (var room in shift.rooms)
                          Padding(
                            padding: const EdgeInsets.only(top: 8),
                            child: Row(
                              children: [
                                const Icon(FluentIcons.room, size: 16),
                                const SizedBox(width: 8),
                                Text(
                                  '${room.name} (${room.location}) - ${room.subject.name}',
                                  style: FluentTheme.of(context)
                                      .typography
                                      .caption,
                                ),
                              ],
                            ),
                          ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
      actions: [
        FilledButton(
          onPressed:
              (_selectedShift == null || _selectedRoom == null || _isLoading)
                  ? null
                  : () async {
                      await _loadAndSaveData(
                        widget.examPeriods.first,
                        _selectedShift!,
                        _selectedRoom!,
                      );
                      if (mounted && !_isLoading) {
                        Navigator.pop(context);
                      }
                    },
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (_isLoading) ...[
                const SizedBox(
                  width: 16,
                  height: 16,
                  child: ProgressRing(),
                ),
                const SizedBox(width: 8),
                const Text('Đang tải...'),
              ] else ...[
                const Icon(FluentIcons.accept, size: 16),
                const SizedBox(width: 8),
                const Text('Xác nhận'),
              ],
            ],
          ),
        ),
      ],
    );
  }
}
