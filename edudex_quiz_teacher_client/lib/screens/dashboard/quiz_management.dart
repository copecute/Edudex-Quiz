import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../../providers/exam_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/tcp_server_provider.dart';
import 'package:flutter/services.dart';
import '../../services/log_service.dart';
import '../../widgets/server_log_view.dart';

class QuizManagementPage extends StatefulWidget {
  const QuizManagementPage({super.key});

  @override
  State<QuizManagementPage> createState() => _QuizManagementPageState();
}

class _QuizManagementPageState extends State<QuizManagementPage> {
  final _examIdController = TextEditingController();
  bool _isServerRunning = false;

  @override
  void dispose() {
    _examIdController.dispose();
    super.dispose();
  }

  void _showInfoBar(BuildContext context, String message) {
    displayInfoBar(
      context,
      duration: const Duration(seconds: 2),
      builder: (context, close) {
        return InfoBar(
          title: Text(message),
          severity: InfoBarSeverity.success,
          onClose: close,
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Quản lý đề thi'),
      ),
      content: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Server control section
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Điều khiển máy chủ',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 16),
                    Consumer<TCPServerProvider>(
                      builder: (context, provider, child) =>
                          _buildServerStatus(provider),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Exam loading section
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Tải đề thi',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        SizedBox(
                          width: 200,
                          child: TextBox(
                            controller: _examIdController,
                            placeholder: 'Nhập mã đề thi',
                          ),
                        ),
                        const SizedBox(width: 16),
                        FilledButton(
                          onPressed: () {
                            final examId = int.tryParse(_examIdController.text);
                            if (examId != null) {
                              context.read<ExamProvider>().loadExam(examId);
                              context
                                  .read<StudentProvider>()
                                  .loadStudents(examId);
                            }
                          },
                          child: const Text('Tải đề thi'),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Students list
            Expanded(
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Danh sách thí sinh',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 16),
                      Expanded(
                        child: Consumer<StudentProvider>(
                          builder: (context, provider, child) {
                            if (provider.isLoading) {
                              return const Center(child: ProgressRing());
                            }

                            if (provider.error != null) {
                              return Center(
                                child: Text(
                                  'Lỗi: ${provider.error}',
                                  style: const TextStyle(
                                      color: Colors.errorPrimaryColor),
                                ),
                              );
                            }

                            if (provider.students.isEmpty) {
                              return const Center(
                                child: Text('Chưa có thí sinh nào'),
                              );
                            }

                            return ListView.builder(
                              itemCount: provider.students.length,
                              itemBuilder: (context, index) {
                                final student = provider.students[index];
                                return ListTile(
                                  leading: Text(student.studentCode),
                                  title: Text(student.name),
                                  trailing: Text(student.status),
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
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildServerStatus(TCPServerProvider provider) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            ToggleSwitch(
              checked: provider.isRunning,
              onChanged: (value) {
                if (value) {
                  provider.startServer();
                } else {
                  provider.stopServer();
                }
              },
              content: Text(provider.isRunning ? 'Đang chạy' : 'Đã dừng'),
            ),
            const SizedBox(width: 16),
            if (provider.isRunning) ...[
              Text('Số thí sinh đang kết nối: ${provider.connectedClients}'),
              const SizedBox(width: 16),
              FutureBuilder<String?>(
                future: provider.getTeacherIp(),
                builder: (context, snapshot) {
                  if (snapshot.hasData && snapshot.data != null) {
                    return Row(
                      children: [
                        Text('IP: ${snapshot.data}'),
                        const SizedBox(width: 8),
                        IconButton(
                          icon: const Icon(FluentIcons.copy),
                          onPressed: () {
                            Clipboard.setData(
                                ClipboardData(text: snapshot.data!));
                            _showInfoBar(
                                context, 'Đã sao chép IP vào clipboard');
                          },
                        ),
                      ],
                    );
                  }
                  return const SizedBox.shrink();
                },
              ),
              const SizedBox(width: 16),
              FilledButton(
                child: const Text('Xem log'),
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (context) => ServerLogView(
                      logs: provider.logs,
                    ),
                  );
                },
              ),
            ],
          ],
        ),
        if (provider.error != null)
          Padding(
            padding: const EdgeInsets.only(top: 8),
            child: Text(
              'Lỗi: ${provider.error}',
              style: const TextStyle(color: Colors.errorPrimaryColor),
            ),
          ),
        const SizedBox(height: 8),
        const Text(
          'Thí sinh có thể kết nối bằng cách:\n'
          '1. Nhập IP thủ công\n'
          '2. Sử dụng tính năng "Tìm tự động" trên phần mềm thí sinh',
          style: TextStyle(fontSize: 12),
        ),
      ],
    );
  }
}
