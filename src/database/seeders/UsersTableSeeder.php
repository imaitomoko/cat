<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'user_id' => 'h001',
        'user_name' => 'tony',
        'email' => 'tony@example.com',
        'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);
        $param = [
        'user_id' => 'h002',
        'user_name' => 'kitty',
        'email' => 'kitty@example.com',
        'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);
        $param = [
        'user_id' => 'h003',
        'user_name' => 'ted',
        'email' => 'ted@example.com',
        'password' => Hash::make('password'),
        ];
        DB::table('users')->insert($param);
    }
}
