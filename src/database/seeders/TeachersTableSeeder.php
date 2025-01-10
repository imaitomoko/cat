<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeachersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'teacher_id' => 't001',
        'teacher_name' => 'AAA',
        'password' => Hash::make('password'),
        ];
        DB::table('teachers')->insert($param);
        $param = [
        'teacher_id' => 't002',
        'teacher_name' => 'SSS',
        'password' => Hash::make('password'),
        ];
        DB::table('teachers')->insert($param);
    }
}
