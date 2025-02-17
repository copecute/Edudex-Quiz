import 'package:intl/intl.dart';

class DateTimeHelper {
  // Chuyển đổi từ UTC sang GMT+7
  static DateTime convertToVNTime(String utcTime) {
    return DateTime.parse(utcTime).add(const Duration(hours: 7));
  }

  // Format ngày giờ dạng dd/MM/yyyy HH:mm
  static String formatDateTime(String utcTime) {
    return DateFormat('dd/MM/yyyy HH:mm').format(convertToVNTime(utcTime));
  }

  // Format giờ dạng HH:mm
  static String formatTime(String utcTime) {
    return DateFormat('HH:mm').format(convertToVNTime(utcTime));
  }
}
