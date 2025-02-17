import 'package:encrypt/encrypt.dart' as encrypt;
import 'dart:developer';

// Key và IV cố định cho toàn bộ ứng dụng
class AppCrypto {
  static final key = encrypt.Key.fromBase64(
      'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXoxMjM0NTY='); // base64 của 32 bytes

  static final iv =
      encrypt.IV.fromBase64('MTIzNDU2Nzg5MDEyMzQ1Ng=='); // base64 của 16 bytes

  static final encrypter = encrypt.Encrypter(encrypt.AES(key));
}
