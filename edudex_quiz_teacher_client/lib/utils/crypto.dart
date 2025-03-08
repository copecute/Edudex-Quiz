import 'package:encrypt/encrypt.dart' as encrypt;
import 'dart:developer';
import 'dart:convert';
import 'dart:typed_data' show Uint8List;

// Key và IV cố định cho toàn bộ ứng dụng - phải giống với client
class AppCrypto {
  static final key = encrypt.Key.fromBase64(
      'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXoxMjM0NTY='); // base64 của 32 bytes

  static final iv =
      encrypt.IV.fromBase64('MTIzNDU2Nzg5MDEyMzQ1Ng=='); // base64 của 16 bytes

  static final encrypter = encrypt.Encrypter(encrypt.AES(key));

  // Mã hóa văn bản thành bytes
  static List<int> encryptToBytes(String text) {
    try {
      final encrypted = encrypter.encrypt(text, iv: iv);
      return encrypted.bytes;
    } catch (e) {
      log('Encryption error: $e');
      rethrow;
    }
  }

  // Giải mã từ bytes
  static String decryptFromBytes(List<int> bytes) {
    try {
      final encrypted = encrypt.Encrypted(Uint8List.fromList(bytes));
      return encrypter.decrypt(encrypted, iv: iv);
    } catch (e) {
      log('Decryption error: $e');
      rethrow;
    }
  }

  // Kiểm tra file có phải định dạng .edudex hợp lệ không
  static bool isValidEdudexFile(List<int> bytes) {
    try {
      // Thử giải mã
      decryptFromBytes(bytes);
      return true;
    } catch (e) {
      return false;
    }
  }

  // Mã hóa văn bản thành base64
  static String encryptToBase64(String text) {
    try {
      final encrypted = encrypter.encrypt(text, iv: iv);
      return encrypted.base64;
    } catch (e) {
      log('Encryption to base64 error: $e');
      rethrow;
    }
  }

  // Giải mã từ base64
  static String decryptFromBase64(String base64Text) {
    try {
      final encrypted = encrypt.Encrypted.fromBase64(base64Text);
      return encrypter.decrypt(encrypted, iv: iv);
    } catch (e) {
      log('Decryption from base64 error: $e',
          error: e, stackTrace: StackTrace.current);
      throw Exception('Không thể giải mã dữ liệu: ${e.toString()}');
    }
  }
}
