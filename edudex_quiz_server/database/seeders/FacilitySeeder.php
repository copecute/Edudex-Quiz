<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'code' => 'MĐ',
                'name' => 'Mỹ Đình',
                'address' => 'Số 18-20 Nhân Mỹ - Mỹ Đình 1 - Quận Nam Từ Liêm - TP. Hà Nội',
                'description' => 'Cơ sở 1 khu vực Mỹ Đình',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TT',
                'name' => 'Thanh Trì',
                'address' => 'Km 3 + 350 Đường Phan Trọng Tuệ - Huyện Thanh Trì - TP.Hà Nội',
                'description' => 'Cơ sở 2 khu vực Thanh Trì',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'HP',
                'name' => 'Hải Phòng',
                'address' => 'Số 176 Quán Trữ - Quận Kiến An - TP. Hải Phòng',
                'description' => 'Cơ sở 3 khu vực Hải Phòng',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('facilities')->insert($facilities);
    }
} 