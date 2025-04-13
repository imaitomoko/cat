<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SendTo;

class SendToSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SendTo::insert([
            [
                'mail_id' => 1,
                'user_lesson_id' => 1,
            ],
            [
                'mail_id' => 2,
                'user_lesson_id' => 3,
            ],
        ]);
        //
    }
}
