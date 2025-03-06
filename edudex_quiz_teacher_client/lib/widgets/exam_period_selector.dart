import 'package:fluent_ui/fluent_ui.dart';
import '../models/exam_schedule.dart';

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
                                            '${room.facility} - ${room.subject.name}',
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
                                      '${room.name} (${room.facility}) - ${room.subject.name}',
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
          onPressed: (_selectedShift == null || _selectedRoom == null)
              ? null
              : () {
                  widget.onSelected(
                    _selectedPeriod ?? widget.examPeriods.first,
                    _selectedShift!,
                    _selectedRoom!,
                  );
                  Navigator.pop(context);
                },
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(FluentIcons.accept, size: 16),
              const SizedBox(width: 8),
              const Text('Xác nhận'),
            ],
          ),
        ),
      ],
    );
  }
}
