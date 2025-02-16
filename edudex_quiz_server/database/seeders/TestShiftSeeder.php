<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestShiftSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('test_shifts')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy danh sách kỳ thi
        $testSessions = DB::table('test_sessions')->get();

        foreach ($testSessions as $session) {
            $sessionStart = Carbon::parse($session->start_time);
            $sessionEnd = Carbon::parse($session->end_time);

            // Ca 1: Đã kết thúc (ngày đầu tiên của kỳ thi)
            DB::table('test_shifts')->insert([
                'name' => $this->getRandomShiftName(),
                'description' => 'Ca thi sáng đã kết thúc',
                'start_time' => $sessionStart->copy()->setTime(7, 30),
                'end_time' => $sessionStart->copy()->setTime(9, 30),
                'test_session_id' => $session->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Ca 2: Đang diễn ra (ngày giữa kỳ thi)
            $midSession = $sessionStart->copy()->addDays(
                ceil($sessionStart->diffInDays($sessionEnd) / 2)
            );
            
            DB::table('test_shifts')->insert([
                'name' => $this->getRandomShiftName(),
                'description' => 'Ca thi giữa đang diễn ra',
                'start_time' => $midSession->copy()->setTime(10, 0),
                'end_time' => $midSession->copy()->setTime(12, 0),
                'test_session_id' => $session->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Ca 3: Trong tương lai (ngày cuối của kỳ thi)
            DB::table('test_shifts')->insert([
                'name' => $this->getRandomShiftName(),
                'description' => 'Ca thi chiều trong tương lai (đã khóa)',
                'start_time' => $sessionEnd->copy()->setTime(14, 0),
                'end_time' => $sessionEnd->copy()->setTime(16, 0),
                'test_session_id' => $session->id,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function getRandomShiftName(): string
    {
        $prefixes = ['Ca', 'Đợt', 'Buổi'];
        $times = ['Sáng', 'Trưa', 'Chiều'];
        $numbers = range(1, 5);

        return sprintf(
            '%s %s %d',
            $prefixes[array_rand($prefixes)],
            $times[array_rand($times)],
            $numbers[array_rand($numbers)]
        );
    }
} 