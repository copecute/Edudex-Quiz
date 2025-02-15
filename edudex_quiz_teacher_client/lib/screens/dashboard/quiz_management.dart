import 'package:fluent_ui/fluent_ui.dart';

class QuizManagementPage extends StatelessWidget {
  const QuizManagementPage({super.key});

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Quản lý đề thi'),
      ),
      content: const Center(
        child: Text('Trang quản lý đề thi'),
      ),
    );
  }
}
