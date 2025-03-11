<?php

namespace App\Http\Controllers;

use App\Utils\EdudexCrypto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EdudexFileController extends Controller
{
    public function index()
    {
        return view('edudex-files.index');
    }

    public function read(Request $request)
    {
        try {
            // kiểm tra file upload
            $request->validate([
                'file' => 'required|file|max:10240', // max 10MB
            ]);

            $file = $request->file('file');
            
            // kiểm tra phần mở rộng
            if ($file->getClientOriginalExtension() !== 'edudex') {
                return response()->json([
                    'success' => false,
                    'message' => 'File phải có định dạng .edudex'
                ], 400);
            }

            // đọc nội dung file dưới dạng bytes
            $fileContents = file_get_contents($file->getRealPath());
            
            // Lưu file gốc để debug
            $debugPath = 'debug/' . $file->getClientOriginalName() . '.debug';
            Storage::put($debugPath, $fileContents);
            
            // Mã hóa base64 cho quá trình giải mã
            $base64Content = base64_encode($fileContents);
            
            // Lưu thông tin debug
            Log::info('Reading edudex file:', [
                'filename' => $file->getClientOriginalName(),
                'size' => strlen($fileContents),
                'first_bytes' => bin2hex(substr($fileContents, 0, 16))
            ]);

            try {
                // Thử giải mã nội dung (sẽ throw exception nếu không thành công)
                $decryptedContent = EdudexCrypto::decryptFromBase64($base64Content);
                
                // Kiểm tra có phải là UTF-8 hợp lệ không
                if (!mb_check_encoding($decryptedContent, 'UTF-8')) {
                    // Thử chuyển đổi sang UTF-8 từ các encoding khác
                    $encodings = ['ASCII', 'ISO-8859-1', 'Windows-1252'];
                    $convertedContent = null;
                    
                    foreach ($encodings as $encoding) {
                        $converted = mb_convert_encoding($decryptedContent, 'UTF-8', $encoding);
                        if (mb_check_encoding($converted, 'UTF-8')) {
                            $convertedContent = $converted;
                            Log::info('Successfully converted from ' . $encoding . ' to UTF-8');
                            break;
                        }
                    }
                    
                    if ($convertedContent !== null) {
                        $decryptedContent = $convertedContent;
                    } else {
                        // Nếu không thể chuyển đổi, coi là dữ liệu nhị phân
                        return response()->json([
                            'success' => true,
                            'is_binary' => true,
                            'content_base64' => base64_encode($decryptedContent),
                            'original_size' => strlen($decryptedContent)
                        ]);
                    }
                }
                
                // Nếu có thể hiển thị dưới dạng text
                return response()->json([
                    'success' => true,
                    'is_binary' => false,
                    'content' => $decryptedContent
                ]);
            } catch (Exception $e) {
                // Nếu giải mã thất bại, thử giải mã trực tiếp từ Flutter client (mô phỏng theo code Flutter)
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể giải mã file .edudex: ' . $e->getMessage()
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Error reading edudex file: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reading edudex file: ' . $e->getMessage()
            ], 500);
        }
    }
} 