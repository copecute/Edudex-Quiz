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

  const ExamPeriodSelector({
    super.key,
    required this.examPeriods,
    required this.onSelected,
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

  Future<void> _loadAndSaveData(
      ExamPeriod period, ExamShift shift, ExamRoom room) async {
    // Tạo BuildContext mới để quản lý dialog
    late BuildContext dialogContext;

    try {
      setState(() => _isLoading = true);

      await _onPeriodSelected();
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('user_token');
      final serverUrl = prefs.getString('server_url');

      // Hiển thị dialog loading
      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (BuildContext context) {
            dialogContext = context;
            return const ContentDialog(
              title: Text('Đang xử lý'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  ProgressRing(),
                  SizedBox(height: 16),
                  Text('Đang tải dữ liệu đề thi và danh sách thí sinh...'),
                ],
              ),
            );
          },
        );
      }

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

      // Đóng dialog loading
      if (mounted) {
        Navigator.pop(dialogContext);
      }

      // Xử lý response
      final examData = json.decode(examResponse.body);
      final studentsData = json.decode(studentsResponse.body);

      // Kiểm tra response có data không
      if (examResponse.statusCode == 200 &&
          studentsResponse.statusCode == 200 &&
          examData['data'] != null &&
          studentsData['data'] != null) {
        // Hiển thị thông báo đang lưu
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => const ContentDialog(
              title: Text('Đang xử lý'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  ProgressRing(),
                  SizedBox(height: 16),
                  Text('Đang lưu dữ liệu vào bộ nhớ...'),
                ],
              ),
            ),
          );
        }

        try {
          // Lưu vào database
          await _examDb.updateExamData(examData['data']);
          await _examDb.updateStudents(studentsData['data']);

          // Kiểm tra dữ liệu đã lưu
          final savedExam = await _examDb.getExamData();
          final savedStudents = await _examDb.getStudents();

          if (savedExam == null || savedStudents.isEmpty) {
            throw Exception('Không thể lưu dữ liệu vào bộ nhớ');
          }

          // Đóng dialog loading và gọi callback
          if (mounted) {
            Navigator.pop(context); // Đóng dialog "Đang lưu"
            widget.onSelected(period, shift, room);
          }
        } catch (e) {
          if (mounted) {
            Navigator.pop(context); // Đóng dialog "Đang lưu" nếu có lỗi
            await _showErrorDialog(
              context,
              'Lỗi lưu dữ liệu',
              'Không thể lưu dữ liệu vào bộ nhớ: $e',
            );
          }
        }
      } else {
        // Xử lý lỗi từ server
        String errorTitle = 'Lỗi';
        String errorMessage = '';

        if (examData['message'] != null) {
          errorTitle = 'Lỗi tải đề thi';
          errorMessage = examData['message'];
        } else if (studentsData['message'] != null) {
          errorTitle = 'Lỗi tải danh sách thí sinh';
          errorMessage = studentsData['message'];
        } else {
          errorMessage =
              'Không thể tải dữ liệu từ máy chủ. Vui lòng thử lại sau.';
        }

        // Hiển thị dialog lỗi
        if (mounted) {
          await _showErrorDialog(context, errorTitle, errorMessage);
        }
      }
    } catch (e) {
      // Đóng dialog loading nếu có lỗi
      if (mounted) {
        Navigator.pop(dialogContext);
        await _showErrorDialog(
          context,
          'Lỗi không mong muốn',
          'Đã xảy ra lỗi: $e\n\nVui lòng thử lại sau hoặc liên hệ hỗ trợ kỹ thuật.',
        );
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _showErrorDialog(
      BuildContext context, String title, String message) async {
    await showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) => ContentDialog(
        title: Row(
          children: [
            const Icon(FluentIcons.error, color: Colors.errorPrimaryColor),
            const SizedBox(width: 8),
            Text(title),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              message,
              style: const TextStyle(height: 1.5),
            ),
            const SizedBox(height: 16),
            const Text(
              'Vui lòng thử lại sau hoặc liên hệ quản trị viên để được hỗ trợ.',
              style: TextStyle(
                fontStyle: FontStyle.italic,
                color: Colors.warningPrimaryColor,
              ),
            ),
          ],
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
