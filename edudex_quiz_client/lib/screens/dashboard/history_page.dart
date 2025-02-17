import 'package:fluent_ui/fluent_ui.dart';
import 'package:encrypt/encrypt.dart' as encrypt;
import 'dart:io';
import '../../utils/crypto.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:developer';
import 'package:file_picker/file_picker.dart';

class HistoryPage extends StatefulWidget {
  final String? initialFile;

  const HistoryPage({
    super.key,
    this.initialFile,
  });

  @override
  State<HistoryPage> createState() => _HistoryPageState();
}

class _HistoryPageState extends State<HistoryPage> {
  String? _selectedContent;
  List<String> _recentFiles = []; // Lưu đường dẫn các file đã mở

  @override
  void initState() {
    super.initState();
    _loadRecentFiles();

    // Nếu có file cần mở, đọc ngay
    if (widget.initialFile != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _readResultFile(widget.initialFile!);
      });
    }
  }

  Future<void> _loadRecentFiles() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _recentFiles = prefs.getStringList('recent_files') ?? [];
    });
  }

  Future<void> _addToRecentFiles(String filePath) async {
    if (!_recentFiles.contains(filePath)) {
      final prefs = await SharedPreferences.getInstance();
      setState(() {
        _recentFiles.insert(0, filePath); // Thêm vào đầu danh sách
        if (_recentFiles.length > 10) {
          // Giới hạn 10 file gần đây
          _recentFiles.removeLast();
        }
      });
      await prefs.setStringList('recent_files', _recentFiles);
    }
  }

  Future<void> _pickAndReadFile() async {
    try {
      // Lấy đường dẫn thư mục Results từ đường dẫn của file đang mở
      String resultDirPath;
      if (widget.initialFile != null) {
        final currentFile = File(widget.initialFile!);
        resultDirPath = currentFile.parent.path;
      } else {
        // Fallback về thư mục ứng dụng nếu không có file ban đầu
        final appDir = Directory.current;
        resultDirPath = '${appDir.path}\\Results';

        // Tạo thư mục nếu chưa tồn tại
        final resultDir = Directory(resultDirPath);
        if (!await resultDir.exists()) {
          await resultDir.create();
        }
      }

      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['edudex'],
        initialDirectory: resultDirPath,
      );

      if (result != null) {
        final file = File(result.files.single.path!);
        await _readResultFile(file.path);
      }
    } catch (e) {
      log('Error picking file: $e');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể đọc file: ${e.toString()}'),
            actions: [
              Button(
                child: const Text('OK'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    }
  }

  Future<void> _readResultFile(String path) async {
    try {
      final file = File(path);

      // Kiểm tra file có tồn tại không
      if (!await file.exists()) {
        // Xóa khỏi danh sách gần đây
        final prefs = await SharedPreferences.getInstance();
        setState(() {
          _recentFiles.remove(path);
        });
        await prefs.setStringList('recent_files', _recentFiles);

        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => ContentDialog(
              title: const Text('Thông báo'),
              content:
                  const Text('File đã bị xóa hoặc di chuyển đến vị trí khác'),
              actions: [
                Button(
                  child: const Text('OK'),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          );
        }
        return;
      }

      final bytes = await file.readAsBytes();
      final encrypted = encrypt.Encrypted(bytes);
      final decrypted =
          AppCrypto.encrypter.decrypt(encrypted, iv: AppCrypto.iv);

      setState(() {
        _selectedContent = decrypted;
      });

      await _addToRecentFiles(path);
    } catch (e) {
      log('Error reading file: $e');
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => ContentDialog(
            title: const Text('Lỗi'),
            content: Text('Không thể đọc file: ${e.toString()}'),
            actions: [
              Button(
                child: const Text('OK'),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Lịch sử bài thi'),
      ),
      content: Row(
        children: [
          // Danh sách file gần đây
          SizedBox(
            width: 300,
            child: Card(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Nút chọn file
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: FilledButton(
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(FluentIcons.open_file),
                          SizedBox(width: 8),
                          Text('Mở file kết quả'),
                        ],
                      ),
                      onPressed: _pickAndReadFile,
                    ),
                  ),

                  const Padding(
                    padding: EdgeInsets.all(16),
                    child: Text(
                      'Đã xem gần đây',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),

                  // Danh sách file
                  Expanded(
                    child: ListView.builder(
                      itemCount: _recentFiles.length,
                      itemBuilder: (context, index) {
                        final filePath = _recentFiles[index];
                        final fileName = filePath.split('\\').last;
                        return ListTile(
                          leading: const Icon(FluentIcons.history),
                          title: Text(fileName),
                          onPressed: () => _readResultFile(filePath),
                        );
                      },
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(width: 16),
          // Nội dung file được chọn
          Expanded(
            child: Card(
              child: _selectedContent != null
                  ? SingleChildScrollView(
                      padding: const EdgeInsets.all(16),
                      child: Text(_selectedContent!),
                    )
                  : const Center(
                      child: Text('Chọn file để xem nội dung'),
                    ),
            ),
          ),
        ],
      ),
    );
  }
}
