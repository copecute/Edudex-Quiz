<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Facility;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $facilities = Facility::all();
        
        foreach ($facilities as $facility) {
            // Tạo 10 phòng cho mỗi cơ sở (2 tầng x 5 phòng)
            for ($floor = 1; $floor <= 2; $floor++) {
                for ($room = 1; $room <= 5; $room++) {
                    // Format: {mã cơ sở}{tầng}{số thứ tự 2 chữ số}
                    // Ví dụ: MĐ101 - Phòng 01 tầng 1 cơ sở MĐ
                    $roomNumber = sprintf("%d%02d", $floor, $room); // 101, 102,...
                    $roomCode = $facility->code . $roomNumber;
                    
                    Room::create([
                        'code' => $roomCode,
                        'name' => $roomCode, // Tên phòng giống mã phòng
                        'facility_id' => $facility->id,
                        'capacity' => rand(15, 20),
                        'description' => "Phòng {$roomNumber} - Tầng {$floor} - {$facility->name}",
                        'is_active' => true
                    ]);
                }
            }
        }
    }
} 