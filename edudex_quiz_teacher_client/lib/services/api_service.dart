import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String API_VERSION = 'v1';

  Future<String?> get serverUrl async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('server_url');
  }

  Future<Map<String, dynamic>> fetchExamData(int examId) async {
    try {
      final url = '${await serverUrl}/api/$API_VERSION/exams/$examId';
      final response = await http.get(
        Uri.parse(url),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to fetch exam data');
      }
    } catch (e) {
      print('Error fetching exam data: $e');
      rethrow;
    }
  }

  Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('user_token');

    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

  Future<void> submitExamResults(
      int examId, List<Map<String, dynamic>> results) async {
    try {
      final url = '${await serverUrl}/api/$API_VERSION/exams/$examId/submit';
      final response = await http.post(
        Uri.parse(url),
        headers: await _getHeaders(),
        body: json.encode({'results': results}),
      );

      if (response.statusCode != 200) {
        throw Exception('Failed to submit exam results');
      }
    } catch (e) {
      print('Error submitting exam results: $e');
      rethrow;
    }
  }
}
