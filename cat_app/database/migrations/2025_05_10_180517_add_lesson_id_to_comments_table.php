<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLessonIdToCommentsTable extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable()->after('id'); // lesson_id カラムを追加
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade'); // 外部キー制約（optional）
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']); // 外部キー制約を削除
            $table->dropColumn('lesson_id'); // lesson_id カラムを削除
        });
    }
}