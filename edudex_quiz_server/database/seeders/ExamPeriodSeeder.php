<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamPeriod;
use App\Models\ExamShift;
use App\Models\ExamPeriodSubject;
use App\Models\ExamPeriodSubjectStudent;
use App\Models\ExamPeriodRoom;
use App\Models\ExamPeriodProctor;
use App\Models\Account;
use App\Models\Subject;
use App\Models\Room;
use App\Models\Facility;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\Exam;

class ExamPeriodSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('vi_VN');
        
        // Tạo kỳ thi mẫu
        $examPeriod = ExamPeriod::create([
            'name' => 'Kỳ thi cuối kỳ 2 năm học 2024-2025',
            'description' => 'Kỳ thi cuối kỳ 2 dành cho sinh viên năm cuối',
            'start_time' => Carbon::create(2025, 4, 1, 7, 0, 0),
            'end_time' => Carbon::create(2025, 4, 30, 23, 59, 59),
            'is_active' => true
        ]);

        // Tạo các ca thi (3 ca một ngày, trong 5 ngày)
        $shiftTimes = [
            [
                'name' => 'Ca sáng',
                'time' => ['07:30:00', '09:30:00']
            ],
            [
                'name' => 'Ca sáng 2',
                'time' => ['09:45:00', '11:45:00']
            ],
            [
                'name' => 'Ca chiều',
                'time' => ['13:30:00', '15:30:00']
            ],
            [
                'name' => 'Ca chiều 2',
                'time' => ['15:45:00', '17:45:00']
            ]
        ];

        for ($day = 11; $day <= 15; $day++) {
            foreach ($shiftTimes as $shift) {
                ExamShift::create([
                    'exam_period_id' => $examPeriod->id,
                    'name' => $shift['name'] . ' ngày ' . $day,
                    'start_time' => Carbon::create(2025, 4, $day, 
                        substr($shift['time'][0], 0, 2), 
                        substr($shift['time'][0], 3, 2)
                    ),
                    'end_time' => Carbon::create(2025, 4, $day,
                        substr($shift['time'][1], 0, 2),
                        substr($shift['time'][1], 3, 2)
                    ),
                    'is_active' => true
                ]);
            }
        }

        // Thêm 10 môn thi vào kỳ thi
        $subjects = Subject::inRandomOrder()->take(10)->get();
        foreach ($subjects as $subject) {
            // Lấy một đề thi ngẫu nhiên của môn học này
            $exam = Exam::where('subject_code', $subject->code)
                        ->inRandomOrder()
                        ->first();

            ExamPeriodSubject::create([
                'exam_period_id' => $examPeriod->id,
                'subject_id' => $subject->id,
                'exam_id' => $exam ? $exam->id : null
            ]);
        }

        // Thêm 20 phòng thi vào kỳ thi
        // Lấy ngẫu nhiên 60% số phòng của mỗi cơ sở
        $facilities = Facility::all();
        foreach ($facilities as $facility) {
            $facilityRooms = Room::where('facility_id', $facility->id)
                                ->inRandomOrder()
                                ->take(12) // 60% của 20 phòng
                                ->get();
            foreach ($facilityRooms as $room) {
                ExamPeriodRoom::create([
                    'exam_period_id' => $examPeriod->id,
                    'room_id' => $room->id
                ]);
            }
        }

        // Thêm 15 cán bộ coi thi
        $proctors = Account::inRandomOrder()->take(15)->get();
        foreach ($proctors as $proctor) {
            ExamPeriodProctor::create([
                'exam_period_id' => $examPeriod->id,
                'account_id' => $proctor->id
            ]);
        }

        // Thêm thí sinh cho mỗi môn thi (30-50 thí sinh/môn)
        $examPeriodSubjects = ExamPeriodSubject::where('exam_period_id', $examPeriod->id)->get();
        foreach ($examPeriodSubjects as $subject) {
            $numStudents = rand(30, 50);
            for ($i = 0; $i < $numStudents; $i++) {
                ExamPeriodSubjectStudent::create([
                    'exam_period_id' => $examPeriod->id,
                    'exam_period_subject_id' => $subject->id,
                    'exam_code' => ExamPeriodSubjectStudent::generateExamCode($subject->id),
                    'student_code' => 'SV' . $faker->unique()->numberBetween(1000, 9999),
                    'full_name' => $faker->name,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'birthday' => $faker->dateTimeBetween('-25 years', '-18 years'),
                    'gender' => $faker->boolean(60) // 60% nam
                ]);
            }
        }
    }
} 