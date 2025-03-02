import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../../providers/exam_provider.dart';
import '../../providers/student_provider.dart';

class QuizManagementPage extends StatefulWidget {
  const QuizManagementPage({super.key});

  @override
  State<QuizManagementPage> createState() => _QuizManagementPageState();
}

class _QuizManagementPageState extends State<QuizManagementPage> {
  final _examIdController = TextEditingController();

  @override
  void dispose() {
    _examIdController.dispose();
    super.dispose();
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
            // Exam management section
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Thông tin đề thi',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        SizedBox(
                          width: 200,
                          child: InfoLabel(
                            label: 'Mã đề thi',
                            child: TextBox(
                              controller: _examIdController,
                              placeholder: 'Nhập mã đề thi',
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        FilledButton(
                          child: const Text('Tải đề thi'),
                          onPressed: () {
                            final examId = int.tryParse(_examIdController.text);
                            if (examId != null) {
                              context.read<ExamProvider>().loadExam(examId);
                            }
                          },
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Exam details
            Consumer<ExamProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: ProgressRing());
                }

                if (provider.error != null) {
                  return InfoBar(
                    title: Text('Lỗi: ${provider.error}'),
                    severity: InfoBarSeverity.error,
                  );
                }

                final exam = provider.currentExam;
                if (exam == null) {
                  return const Center(
                    child: Text('Chọn đề thi để xem chi tiết'),
                  );
                }

                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          exam.title,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text('Thời gian: ${exam.duration} phút'),
                        Text('Số câu hỏi: ${exam.totalQuestions}'),
                      ],
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
