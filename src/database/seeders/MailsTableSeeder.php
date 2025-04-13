<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mails')->insert([
            [
                'subject' => 'テストメール1',
                'body' => 'これはテストメールの本文です。',
                'user_lesson_id' => 1, // 既存のuser_lesson_idを指定（実際のIDを指定）
                'attachment' => 'test1.pdf',
                'sent_at' => Carbon::now()->subMonths(1), // 1ヶ月前の送信日
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'subject' => 'テストメール2',
                'body' => 'これは別のテストメールの本文です。',
                'user_lesson_id' => 2, // 他のuser_lesson_idを指定
                'attachment' => 'test2.pdf',
                'sent_at' => Carbon::now()->subWeeks(3), // 3週間前の送信日
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
        ]);
        //
    }
}
