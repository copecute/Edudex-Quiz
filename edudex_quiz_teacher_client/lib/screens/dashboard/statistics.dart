import 'package:fluent_ui/fluent_ui.dart';

class StatisticsPage extends StatelessWidget {
  const StatisticsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Thống kê'),
      ),
      content: const Center(
        child: Text('Trang thống kê'),
      ),
    );
  }
}
