<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'lesson_id' => '2024HFF',
        'year'=> 2024,
        'school_id' => 1,
        'class_id' => 1,
        'day1' => '金',
        'start_time1' => '16:10',
        'duration1' => 50,
        'max_number' => 8,
        'lesson_value' => '1',
        ];
        DB::table('lessons')->insert($param);
        $param = [
        'lesson_id' => '2024HIPM',
        'year'=> 2024,
        'school_id' => 2,
        'class_id' => 4,
        'day1' => '月',
        'start_time1' => '16:10',
        'duration1' => 50,
        'max_number' => 8,
        'lesson_value' => '1',
        ];
        DB::table('lessons')->insert($param);
        $param = [
        'lesson_id' => '2024HG1',
        'year'=> 2024,
        'school_id' => 1,
        'class_id' => 2,
        'day1' => '月',
        'start_time1' => '16:30',
        'duration1' => 120,
        'day2' => '木',
        'start_time2' => '16:30',
        'duration2' => 120,
        'max_number' => 20,
        'lesson_value' => '2',
        ];
        DB::table('lessons')->insert($param);
        //
    }
}
