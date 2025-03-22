<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserLessonStatus;
use App\Models\UserLesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserLessonStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'user_lesson_id' => 3,
            'date' => Carbon::parse('2025-03-07')->toDateString(),  // Carbon を使って日付をフォーマット
            'status' => '未受講',
            'reschedule_to' => null,
        ];
        DB::table('user_lesson_statuses')->insert($param);

        $param = [
            'user_lesson_id' => 5,
            'date' => Carbon::parse('2025-03-24')->toDateString(),  // Carbon を使って日付をフォーマット
            'status' => '未受講',
            'reschedule_to' => null,
        ];
        DB::table('user_lesson_statuses')->insert($param);
        //
    }
}
