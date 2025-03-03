<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            [
                'name' => 'OOP',
                'subject_code' => 'INT1234', // Java
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Collection',
                'subject_code' => 'INT1234', // Java
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SQL',
                'subject_code' => 'INT1235', // CSDL
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Normalization',
                'subject_code' => 'INT1235', // CSDL
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HTML',
                'subject_code' => 'INT1236', // Web
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CSS',
                'subject_code' => 'INT1236', // Web
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'UML',
                'subject_code' => 'INT1237', // PTTKHT
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Digital Logic',
                'subject_code' => 'ELE1234', // Kỹ thuật số
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tags')->insert($tags);
    }
} 