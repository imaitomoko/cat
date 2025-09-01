<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCommentsTable extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            // 不要なカラムを削除（あるなら）
            $table->dropColumn('lesson_id');

        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
    
            $table->unsignedBigInteger('lesson_id')->nullable();
        });
    }
}