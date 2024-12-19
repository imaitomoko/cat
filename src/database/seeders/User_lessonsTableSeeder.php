<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class User_lessonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'user_id' => 1,
        'lesson_id' => 1,
        'status' => '未受講',
        ];
        DB::table('User_lessons')->insert($param);
        $param = [
        'user_id' => 1,
        'lesson_id' => 2,
        'status' => '欠席',
        ];
        DB::table('User_lessons')->insert($param);
        $param = [
        'user_id' => 2,
        'lesson_id' => 1,
        'status' => '完了',
        ];
        DB::table('User_lessons')->insert($param);
        //
    }
}
