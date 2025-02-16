<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestRoomSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('test_rooms')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy danh sách location_id
        $locations = DB::table('test_locations')->get();

        foreach ($locations as $location) {
            $rooms = $this->getRoomsForLocation($location->name);
            
            foreach ($rooms as $room) {
                DB::table('test_rooms')->insert([
                    'code' => $room['code'],
                    'name' => $room['name'],
                    'capacity' => $room['capacity'],
                    'test_location_id' => $location->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function getRoomsForLocation(string $locationName): array
    {
        $roomsData = [
            'Mỹ Đình' => [
                [
                    'code' => 'MD101',
                    'name' => 'Phòng 101',
                    'capacity' => 30
                ],
                [
                    'code' => 'MD102',
                    'name' => 'Phòng 102',
                    'capacity' => 30
                ],
                [
                    'code' => 'MD201',
                    'name' => 'Phòng 201',
                    'capacity' => 40
                ],
                [
                    'code' => 'MD202',
                    'name' => 'Phòng 202',
                    'capacity' => 40
                ],
                [
                    'code' => 'MD301',
                    'name' => 'Phòng 301',
                    'capacity' => 35
                ]
            ],
            'Thanh Trì' => [
                [
                    'code' => 'TT101',
                    'name' => 'Phòng 101',
                    'capacity' => 35
                ],
                [
                    'code' => 'TT102',
                    'name' => 'Phòng 102',
                    'capacity' => 35
                ],
                [
                    'code' => 'TT201',
                    'name' => 'Phòng 201',
                    'capacity' => 45
                ],
                [
                    'code' => 'TT202',
                    'name' => 'Phòng 202',
                    'capacity' => 45
                ]
            ],
            'Hải Phòng' => [
                [
                    'code' => 'HP101',
                    'name' => 'Phòng 101',
                    'capacity' => 30
                ],
                [
                    'code' => 'HP102',
                    'name' => 'Phòng 102',
                    'capacity' => 30
                ],
                [
                    'code' => 'HP201',
                    'name' => 'Phòng 201',
                    'capacity' => 40
                ]
            ]
        ];

        return $roomsData[$locationName] ?? [];
    }
} 