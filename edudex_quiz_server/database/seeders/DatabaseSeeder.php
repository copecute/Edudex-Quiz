<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AccountSeeder::class,
            FacultySeeder::class,
            MajorSeeder::class,
            FacilitySeeder::class,
            RoomSeeder::class,
            SubjectSeeder::class,
            TagSeeder::class,
            QuestionSeeder::class,
            ExamSeeder::class,
            ExamPeriodSeeder::class,
            ExamShiftSeeder::class,
        ]);
    }
}
