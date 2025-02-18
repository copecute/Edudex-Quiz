import 'package:intl/intl.dart';

class DateTimeHelper {
  // Chuyển đổi từ UTC sang GMT+7
  static DateTime convertToVNTime(String utcTime) {
    return DateTime.parse(utcTime).add(const Duration(hours: 7));
  }

  // Format ngày giờ dạng dd/MM/yyyy HH:mm để hiển thị
  static String formatDateTime(String utcTime) {
    return DateFormat('dd/MM/yyyy HH:mm').format(convertToVNTime(utcTime));
  }

  // Format giờ dạng HH:mm để hiển thị
  static String formatTime(String utcTime) {
    return DateFormat('HH:mm').format(convertToVNTime(utcTime));
  }

  // Format ngày giờ dạng ISO 8601 để gửi lên server
  static String formatDateTimeForAPI(DateTime dateTime) {
    // Trừ đi 7 giờ để chuyển về UTC trước khi gửi lên server
    final utcDateTime = dateTime.subtract(const Duration(hours: 7));
    return utcDateTime.toIso8601String();
  }
}
