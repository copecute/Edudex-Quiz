<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('test_locations')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Dữ liệu các địa điểm thi
        $locations = [
            [
                'name' => 'Mỹ Đình',
                'address' => 'Số 298 Đường Cầu Diễn, Phường Minh Khai, Quận Nam Từ Liêm, Hà Nội',
                'description' => 'Cơ sở đào tạo chính tại khu vực Mỹ Đình - Hà Nội',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Thanh Trì',
                'address' => 'Hà Nội',
                'description' => 'Cơ sở đào tạo tại khu vực Thanh Trì - Hà Nội',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Hải Phòng',
                'address' => 'Hải Phòng',
                'description' => 'Cơ sở đào tạo tại thành phố Hải Phòng',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Thêm dữ liệu vào bảng
        DB::table('test_locations')->insert($locations);
    }
} 