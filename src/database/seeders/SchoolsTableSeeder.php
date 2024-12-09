<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'school_name' => '本校',
        ];
        DB::table('schools')->insert($param);
        $param = [
        'school_name' => '東神吉校',
        ];
        DB::table('schools')->insert($param);
        $param = [
        'school_name' => 'みのりヶ丘校',
        ];
        DB::table('schools')->insert($param);

        //
    }
}
