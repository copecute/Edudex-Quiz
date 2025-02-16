<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultyMajorSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo dữ liệu cho các khoa
        $faculties = [
            [
                'code' => 'CNTT',
                'name' => 'Khoa Công nghệ thông tin',
                'description' => 'Đào tạo các chuyên ngành về công nghệ thông tin',
            ],
            [
                'code' => 'KT',
                'name' => 'Khoa Kinh tế',
                'description' => 'Đào tạo các chuyên ngành về kinh tế và quản trị',
            ],
            [
                'code' => 'DT',
                'name' => 'Khoa Điện tử',
                'description' => 'Đào tạo các chuyên ngành về điện tử và viễn thông',
            ],
            [
                'code' => 'CK',
                'name' => 'Khoa Cơ khí',
                'description' => 'Đào tạo các chuyên ngành về cơ khí và tự động hóa',
            ],
        ];

        // Thêm dữ liệu vào bảng faculties
        foreach ($faculties as $faculty) {
            $facultyId = DB::table('faculties')->insertGetId([
                'code' => $faculty['code'],
                'name' => $faculty['name'],
                'description' => $faculty['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Tạo các ngành cho từng khoa
            $majors = $this->getMajorsForFaculty($faculty['code'], $facultyId);
            
            // Thêm dữ liệu vào bảng majors
            foreach ($majors as $major) {
                DB::table('majors')->insert([
                    'code' => $major['code'],
                    'name' => $major['name'],
                    'description' => $major['description'],
                    'faculty_id' => $facultyId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function getMajorsForFaculty(string $facultyCode, int $facultyId): array
    {
        $majorsData = [
            'CNTT' => [
                [
                    'code' => 'CNTT-KTPM',
                    'name' => 'Kỹ thuật phần mềm',
                    'description' => 'Chuyên ngành về phát triển phần mềm',
                ],
                [
                    'code' => 'CNTT-HTTT',
                    'name' => 'Hệ thống thông tin',
                    'description' => 'Chuyên ngành về hệ thống thông tin quản lý',
                ],
                [
                    'code' => 'CNTT-MMT',
                    'name' => 'Mạng máy tính',
                    'description' => 'Chuyên ngành về mạng và bảo mật',
                ],
            ],
            'KT' => [
                [
                    'code' => 'KT-QTKD',
                    'name' => 'Quản trị kinh doanh',
                    'description' => 'Chuyên ngành về quản trị doanh nghiệp',
                ],
                [
                    'code' => 'KT-TCNH',
                    'name' => 'Tài chính ngân hàng',
                    'description' => 'Chuyên ngành về tài chính và ngân hàng',
                ],
                [
                    'code' => 'KT-KTQT',
                    'name' => 'Kế toán quản trị',
                    'description' => 'Chuyên ngành về kế toán và kiểm toán',
                ],
            ],
            'DT' => [
                [
                    'code' => 'DT-DTVT',
                    'name' => 'Điện tử viễn thông',
                    'description' => 'Chuyên ngành về thiết bị điện tử và viễn thông',
                ],
                [
                    'code' => 'DT-KTDT',
                    'name' => 'Kỹ thuật điện tử',
                    'description' => 'Chuyên ngành về thiết kế và chế tạo thiết bị điện tử',
                ],
            ],
            'CK' => [
                [
                    'code' => 'CK-CKCT',
                    'name' => 'Cơ khí chế tạo',
                    'description' => 'Chuyên ngành về thiết kế và chế tạo máy',
                ],
                [
                    'code' => 'CK-TDH',
                    'name' => 'Tự động hóa',
                    'description' => 'Chuyên ngành về hệ thống tự động hóa',
                ],
                [
                    'code' => 'CK-CKOTO',
                    'name' => 'Cơ khí ô tô',
                    'description' => 'Chuyên ngành về kỹ thuật ô tô',
                ],
            ],
        ];

        return $majorsData[$facultyCode] ?? [];
    }
} 