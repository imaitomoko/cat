<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\School;

class SchoolsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        School::truncate();// 既存のデータを削除

        $schools = [
            [
                'school_name' => '本校',
                'en_school_name' => 'Main',
            ],
            [
                'school_name' => '東神吉校',
                'en_school_name' => 'Higashikanki',
            ],
            [
                'school_name' => 'みのりヶ丘校',
                'en_school_name' => 'Minorigaoka',
            ],
        ];

        foreach ($schools as $param) {
            School::create($param);
        }

        // 外部キー制約を有効化
        Schema::enableForeignKeyConstraints();
        //
    }
}
