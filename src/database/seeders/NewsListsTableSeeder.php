<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsListsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
        'news_list' => 'test...teat...teat...teat...teat...teat...',
        ];
        DB::table('news_lists')->insert($param);
        $param = [
        'news_list' => 'bbb...bbbb',
        ];
        DB::table('news_lists')->insert($param);


        //
    }
}
