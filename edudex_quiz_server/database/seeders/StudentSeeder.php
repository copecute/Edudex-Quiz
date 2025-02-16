<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Major;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $majors = Major::all();

        // Tạo 20 thí sinh mẫu
        for ($i = 0; $i < 20; $i++) {
            $student = Student::create([
                'code' => 'SV' . str_pad($i + 1, 6, '0', STR_PAD_LEFT), // SV000001
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->numerify('0#########'), // 10 số, bắt đầu bằng 0
                'address' => $faker->address,
                'birthday' => $faker->dateTimeBetween('-25 years', '-18 years'),
                'gender' => $faker->boolean,
                'status' => $faker->boolean(80), // 80% đang học
            ]);

            // Gán ngẫu nhiên 1-2 ngành học
            $selectedMajors = $majors->random(rand(1, 2));
            foreach ($selectedMajors as $index => $major) {
                $student->majors()->attach($major->id, [
                    'is_main' => $index === 0 // Ngành đầu tiên là ngành chính
                ]);
            }
        }
    }
} 