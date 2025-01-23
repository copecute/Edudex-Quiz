import 'package:fluent_ui/fluent_ui.dart';
import '../quiz_screen.dart';

class QuizMenuPage extends StatelessWidget {
  const QuizMenuPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Card(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Bài kiểm tra cuối kỳ',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            const Text('Thời gian: 30 phút'),
            const Text('Số câu hỏi: 20'),
            const SizedBox(height: 24),
            FilledButton(
              child: const Text('Bắt đầu làm bài'),
              onPressed: () {
                Navigator.push(
                  context,
                  FluentPageRoute(
                    builder: (context) => const QuizScreen(),
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
