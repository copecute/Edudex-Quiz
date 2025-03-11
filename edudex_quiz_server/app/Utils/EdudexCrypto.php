<?php

namespace App\Utils;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EdudexCrypto
{
    // key và iv cố định cho toàn bộ ứng dụng
    private static string $key = 'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXoxMjM0NTY='; // base64 của 32 bytes
    private static string $iv = 'MTIzNDU2Nzg5MDEyMzQ1Ng=='; // base64 của 16 bytes

    /**
     * giải mã dữ liệu từ base64, tương thích với Flutter's encrypt package
     */
    public static function decryptFromBase64(string $base64Text): string
    {
        try {
            // Decode key và iv từ base64
            $key = base64_decode(self::$key);
            $iv = base64_decode(self::$iv);
            
            // Decode dữ liệu đầu vào từ base64
            $encrypted = base64_decode($base64Text);
            
            // Log thông tin để debug
            Log::info('Decryption attempt with:', [
                'key_length' => strlen($key),
                'iv_length' => strlen($iv),
                'data_length' => strlen($encrypted)
            ]);

            // 1. Thử giải mã với phương pháp aes-256-cbc tiêu chuẩn
            $decrypted = @openssl_decrypt(
                $encrypted,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                // 2. Thử lại với PKCS7 padding thủ công
                $decrypted = @openssl_decrypt(
                    $encrypted,
                    'aes-256-cbc',
                    $key,
                    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                    $iv
                );
                
                if ($decrypted === false) {
                    throw new Exception('Không thể giải mã dữ liệu với bất kỳ phương pháp nào');
                }
                
                // xử lý PKCS7 padding
                $paddingChar = ord($decrypted[strlen($decrypted) - 1]);
                if ($paddingChar <= 16) {
                    $decrypted = substr($decrypted, 0, -$paddingChar);
                }
            }
            
            // Lưu dữ liệu giải mã và thông tin để debug
            Storage::put('debug/decrypted_content.bin', $decrypted);
            
            // Log kết quả
            Log::info('Decryption result:', [
                'length' => strlen($decrypted),
                'first_bytes_hex' => bin2hex(substr($decrypted, 0, 16)),
                'is_binary' => self::isBinary($decrypted),
                'is_utf8' => mb_check_encoding($decrypted, 'UTF-8')
            ]);
            
            return $decrypted;
        } catch (Exception $e) {
            Log::error('Decryption failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kiểm tra content có phải là dữ liệu nhị phân không
     */
    private static function isBinary(string $content): bool
    {
        $length = strlen($content);
        $nonPrintable = 0;
        
        // Kiểm tra 100 byte đầu hoặc cả file nếu nhỏ hơn
        for ($i = 0; $i < min($length, 100); $i++) {
            $byte = ord($content[$i]);
            // Bỏ qua một số ký tự điều khiển phổ biến
            if (($byte < 32 && !in_array($byte, [9, 10, 13])) || $byte > 126) {
                $nonPrintable++;
            }
        }
        
        // Nếu có nhiều hơn 10% là ký tự không hiển thị thì coi là dữ liệu nhị phân
        return ($nonPrintable / min($length, 100)) > 0.1;
    }

    /**
     * kiểm tra file có phải định dạng .edudex hợp lệ không
     */
    public static function isValidEdudexFile(string $content): bool
    {
        try {
            // Thử decode base64 trước
            $data = base64_decode($content, true);
            if ($data === false) {
                return false;
            }
            
            // Nếu data bắt đầu bằng flag đặc biệt như 'Salted__', cắt bỏ đi
            if (strlen($data) > 8 && substr($data, 0, 8) === 'Salted__') {
                $data = substr($data, 8);
            }
            
            // Nếu độ dài không chia hết cho 16 (block size của AES), thêm padding
            $blockSize = 16;
            $padLength = $blockSize - (strlen($data) % $blockSize);
            if ($padLength < $blockSize) {
                $data .= str_repeat(chr($padLength), $padLength);
            }
            
            // Encode lại thành base64 để sử dụng cho hàm decrypt
            $processedBase64 = base64_encode($data);
            
            // Thử giải mã
            self::decryptFromBase64($processedBase64);
            return true;
        } catch (Exception $e) {
            Log::warning('Invalid edudex file: ' . $e->getMessage());
            return false;
        }
    }
} 