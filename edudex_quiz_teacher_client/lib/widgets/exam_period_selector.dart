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
          // Tự động chọn nếu chỉ có 1 kỳ thi, 1 ca thi và 1 phòng thi
          WidgetsBinding.instance.addPostFrameCallback((_) {
            widget.onSelected(
                _selectedPeriod!, _selectedShift!, _selectedRoom!);
            Navigator.pop(context);
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
    try {
      setState(() => _isLoading = true);

      // Lưu thông tin session trước
      await _onPeriodSelected();

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('user_token');
      final serverUrl = prefs.getString('server_url');

      // Hiển thị thông báo đang tải
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Đang xử lý'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const ProgressRing(),
                const SizedBox(height: 16),
                const Text('Đang tải dữ liệu đề thi và danh sách thí sinh...'),
              ],
            ),
          ),
        );
      }

      // Tải dữ liệu đề thi
      final examResponse = await http.get(
        Uri.parse(
            '$serverUrl/api/exam-schedule/shifts/${shift.id}/rooms/${room.id}/exam'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      // Tải danh sách thí sinh
      final studentsResponse = await http.get(
        Uri.parse(
            '$serverUrl/api/exam-schedule/shifts/${shift.id}/rooms/${room.id}/students'),
        headers: {
          'Authorization': 'copecute $token',
          'Accept': 'application/json',
        },
      );

      if (mounted) Navigator.pop(context); // Đóng dialog loading

      final examData = json.decode(examResponse.body);
      final studentsData = json.decode(studentsResponse.body);

      if (examData['status'] == 'success' &&
          studentsData['status'] == 'success') {
        // Hiển thị thông báo đang lưu
        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => ContentDialog(
              title: const Text('Đang xử lý'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const ProgressRing(),
                  const SizedBox(height: 16),
                  const Text('Đang lưu dữ liệu vào bộ nhớ...'),
                ],
              ),
            ),
          );
        }

        // Lưu vào database
        await _examDb.updateExamData(examData['data']);
        await _examDb.updateStudents(studentsData['data']);

        // Kiểm tra dữ liệu đã lưu
        final savedExam = await _examDb.getExamData();
        final savedStudents = await _examDb.getStudents();

        if (savedExam == null || savedStudents.isEmpty) {
          throw Exception('Không thể lưu dữ liệu vào bộ nhớ');
        }

        // Chỉ gọi callback một lần ở đây sau khi mọi thứ đã lưu xong
        if (mounted) {
          widget.onSelected(period, shift, room);
        }
      } else {
        throw Exception(
            'Lỗi từ máy chủ:\n${examData['message'] ?? studentsData['message'] ?? 'Không xác định'}');
      }
    } catch (e) {
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Không thể tải hoặc lưu dữ liệu:'),
                const SizedBox(height: 8),
                Text(
                  e.toString(),
                  style: const TextStyle(color: Colors.errorPrimaryColor),
                ),
                const SizedBox(height: 16),
                const Text('Vui lòng thử lại sau hoặc liên hệ hỗ trợ.'),
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
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ContentDialog(
      constraints: const BoxConstraints(maxWidth: 600),
      title: Row(
        children: [
          const Icon(FluentIcons.calendar, size: 24),
          const SizedBox(width: 8),
          const Text('Chọn kỳ thi và ca thi'),
        ],
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (widget.examPeriods.length > 1) ...[
            InfoLabel(
              label: 'Kỳ thi',
              child: ComboBox<ExamPeriod>(
                placeholder: const Text('Chọn kỳ thi...'),
                value: _selectedPeriod,
                items: widget.examPeriods.map((period) {
                  return ComboBoxItem<ExamPeriod>(
                    value: period,
                    child: Text(period.name),
                  );
                }).toList(),
                onChanged: (period) {
                  setState(() {
                    _selectedPeriod = period;
                    _selectedShift = null;
                    _selectedRoom = null;
                  });
                },
              ),
            ),
            const SizedBox(height: 16),
          ],
          if (_selectedPeriod != null || widget.examPeriods.length == 1) ...[
            InfoLabel(
              label: 'Ca thi',
              child: Card(
                padding: const EdgeInsets.all(8),
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: (_selectedPeriod ?? widget.examPeriods.first)
                      .shifts
                      .length,
                  separatorBuilder: (context, index) => const Divider(),
                  itemBuilder: (context, index) {
                    final shift = (_selectedPeriod ?? widget.examPeriods.first)
                        .shifts[index];
                    return RadioButton(
                      checked: _selectedShift == shift,
                      onChanged: (value) {
                        setState(() {
                          _selectedShift = shift;
                          _selectedRoom = shift.rooms.length == 1
                              ? shift.rooms.first
                              : null;
                        });
                      },
                      content: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            shift.name,
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Icon(FluentIcons.clock, size: 12),
                              const SizedBox(width: 4),
                              Text(
                                '${_formatDateTime(shift.startTime)} - ${_formatDateTime(shift.endTime)}',
                                style:
                                    FluentTheme.of(context).typography.caption,
                              ),
                            ],
                          ),
                          if (shift.rooms.length > 1 &&
                              _selectedShift == shift) ...[
                            const SizedBox(height: 8),
                            InfoLabel(
                              label: 'Chọn phòng thi',
                              child: Wrap(
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
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
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
                            ),
                          ] else
                            for (var room in shift.rooms)
                              Padding(
                                padding: const EdgeInsets.only(top: 4),
                                child: Row(
                                  children: [
                                    const Icon(FluentIcons.room, size: 12),
                                    const SizedBox(width: 4),
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
                    );
                  },
                ),
              ),
            ),
          ],
        ],
      ),
      actions: [
        FilledButton(
          onPressed:
              (_selectedShift == null || _selectedRoom == null || _isLoading)
                  ? null
                  : () async {
                      // Lưu thông tin vào database trước
                      await _loadAndSaveData(
                        _selectedPeriod ?? widget.examPeriods.first,
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
