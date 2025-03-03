<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'code' => 'A101',
                'name' => 'Phòng A101',
                'facility_id' => 1,
                'capacity' => 30,
                'description' => 'Phòng học lý thuyết tầng 1 tòa A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'A102',
                'name' => 'Phòng A102',
                'facility_id' => 1,
                'capacity' => 40,
                'description' => 'Phòng học lý thuyết tầng 1 tòa A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'B201',
                'name' => 'Phòng B201',
                'facility_id' => 2,
                'capacity' => 35,
                'description' => 'Phòng học lý thuyết tầng 2 tòa B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'C301',
                'name' => 'Phòng C301',
                'facility_id' => 3,
                'capacity' => 45,
                'description' => 'Phòng học lý thuyết tầng 3 tòa C',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rooms')->insert($rooms);
    }
} 