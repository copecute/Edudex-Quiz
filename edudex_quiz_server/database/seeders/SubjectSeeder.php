<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('subjects')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy danh sách major_id từ bảng majors
        $majors = DB::table('majors')->get();

        foreach ($majors as $major) {
            $subjects = $this->getSubjectsForMajor($major->code);
            
            foreach ($subjects as $subject) {
                DB::table('subjects')->insert([
                    'code' => $subject['code'],
                    'name' => $subject['name'],
                    'description' => $subject['description'],
                    'credits' => $subject['credits'],
                    'major_id' => $major->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function getSubjectsForMajor(string $majorCode): array
    {
        $subjectsData = [
            'CNTT-KTPM' => [
                [
                    'code' => 'KTPM001',
                    'name' => 'Nhập môn Công nghệ phần mềm',
                    'description' => 'Giới thiệu về quy trình phát triển phần mềm',
                    'credits' => 3
                ],
                [
                    'code' => 'KTPM002',
                    'name' => 'Kiểm thử phần mềm',
                    'description' => 'Các kỹ thuật kiểm thử phần mềm',
                    'credits' => 3
                ],
                [
                    'code' => 'KTPM003',
                    'name' => 'Phân tích thiết kế hệ thống',
                    'description' => 'Phương pháp phân tích và thiết kế hệ thống',
                    'credits' => 4
                ]
            ],
            'CNTT-HTTT' => [
                [
                    'code' => 'HTTT001',
                    'name' => 'Cơ sở dữ liệu',
                    'description' => 'Nguyên lý cơ sở dữ liệu',
                    'credits' => 4
                ],
                [
                    'code' => 'HTTT002',
                    'name' => 'Phân tích dữ liệu',
                    'description' => 'Phương pháp phân tích dữ liệu',
                    'credits' => 3
                ],
                [
                    'code' => 'HTTT003',
                    'name' => 'Kho dữ liệu',
                    'description' => 'Thiết kế và quản lý kho dữ liệu',
                    'credits' => 3
                ]
            ],
            'CNTT-MMT' => [
                [
                    'code' => 'MMT001',
                    'name' => 'Mạng máy tính',
                    'description' => 'Kiến trúc và nguyên lý mạng máy tính',
                    'credits' => 4
                ],
                [
                    'code' => 'MMT002',
                    'name' => 'An toàn mạng',
                    'description' => 'Các kỹ thuật bảo mật mạng',
                    'credits' => 3
                ],
                [
                    'code' => 'MMT003',
                    'name' => 'Quản trị mạng',
                    'description' => 'Quản trị hệ thống mạng',
                    'credits' => 3
                ]
            ],
            'KT-QTKD' => [
                [
                    'code' => 'QTKD001',
                    'name' => 'Quản trị học',
                    'description' => 'Nguyên lý quản trị trong doanh nghiệp',
                    'credits' => 3
                ],
                [
                    'code' => 'QTKD002',
                    'name' => 'Marketing căn bản',
                    'description' => 'Nguyên lý marketing',
                    'credits' => 3
                ]
            ],
            'KT-TCNH' => [
                [
                    'code' => 'TCNH001',
                    'name' => 'Tài chính tiền tệ',
                    'description' => 'Lý thuyết tài chính tiền tệ',
                    'credits' => 3
                ],
                [
                    'code' => 'TCNH002',
                    'name' => 'Nghiệp vụ ngân hàng',
                    'description' => 'Các nghiệp vụ cơ bản trong ngân hàng',
                    'credits' => 3
                ]
            ],
            'KT-KTQT' => [
                [
                    'code' => 'KTQT001',
                    'name' => 'Nguyên lý kế toán',
                    'description' => 'Các nguyên lý cơ bản của kế toán',
                    'credits' => 3
                ],
                [
                    'code' => 'KTQT002',
                    'name' => 'Kế toán tài chính',
                    'description' => 'Kế toán trong lĩnh vực tài chính',
                    'credits' => 3
                ]
            ],
            'DT-DTVT' => [
                [
                    'code' => 'DTVT001',
                    'name' => 'Kỹ thuật số',
                    'description' => 'Cơ sở kỹ thuật số',
                    'credits' => 3
                ],
                [
                    'code' => 'DTVT002',
                    'name' => 'Hệ thống viễn thông',
                    'description' => 'Nguyên lý hệ thống viễn thông',
                    'credits' => 4
                ]
            ],
            'DT-KTDT' => [
                [
                    'code' => 'KTDT001',
                    'name' => 'Điện tử cơ bản',
                    'description' => 'Nguyên lý điện tử',
                    'credits' => 3
                ],
                [
                    'code' => 'KTDT002',
                    'name' => 'Vi xử lý',
                    'description' => 'Kiến trúc và lập trình vi xử lý',
                    'credits' => 4
                ]
            ],
            'CK-CKCT' => [
                [
                    'code' => 'CKCT001',
                    'name' => 'Nguyên lý máy',
                    'description' => 'Cơ sở nguyên lý máy',
                    'credits' => 3
                ],
                [
                    'code' => 'CKCT002',
                    'name' => 'Công nghệ chế tạo máy',
                    'description' => 'Quy trình công nghệ chế tạo máy',
                    'credits' => 4
                ]
            ],
            'CK-TDH' => [
                [
                    'code' => 'TDH001',
                    'name' => 'Điều khiển tự động',
                    'description' => 'Lý thuyết điều khiển tự động',
                    'credits' => 3
                ],
                [
                    'code' => 'TDH002',
                    'name' => 'PLC',
                    'description' => 'Lập trình PLC',
                    'credits' => 3
                ]
            ],
            'CK-CKOTO' => [
                [
                    'code' => 'CKOTO001',
                    'name' => 'Kết cấu ô tô',
                    'description' => 'Cấu tạo và nguyên lý hoạt động của ô tô',
                    'credits' => 4
                ],
                [
                    'code' => 'CKOTO002',
                    'name' => 'Động cơ đốt trong',
                    'description' => 'Nguyên lý động cơ đốt trong',
                    'credits' => 3
                ]
            ]
        ];

        return $subjectsData[$majorCode] ?? [];
    }
} 