import 'package:fluent_ui/fluent_ui.dart';

class StudentManagementPage extends StatelessWidget {
  const StudentManagementPage({super.key});

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Quản lý thí sinh'),
      ),
      content: const Center(
        child: Text('Trang quản lý thí sinh'),
      ),
    );
  }
}
