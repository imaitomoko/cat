<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'class_name' => 'Fish',
        ];
        DB::table('classes')->insert($param);
        $param = [
        'class_name' => 'G1',
        ];
        DB::table('classes')->insert($param);
        $param = [
        'class_name' => 'JL4',
        ];
        DB::table('classes')->insert($param);
        $param = [
        'class_name' => 'Panda',
        ];
        DB::table('classes')->insert($param);
        $param = [
        'class_name' => 'Basic',
        ];
        DB::table('classes')->insert($param);
        //
    }
}
