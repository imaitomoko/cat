<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRescheduleToColumnTypeInUserLessonStatusesTable extends Migration
{
    public function up()
    {
        Schema::table('user_lesson_statuses', function (Blueprint $table) {
        // 外部キー制約を削除（制約名に注意！）
            $table->dropForeign(['reschedule_to']);
        });
        
        Schema::table('user_lesson_statuses', function (Blueprint $table) {
            $table->dropColumn('reschedule_to'); // 既存カラムを削除（中身も消えるので注意）
        });

        Schema::table('user_lesson_statuses', function (Blueprint $table) {
            $table->date('reschedule_to')->nullable()->after('status'); // date型で再作成
        });
    }

    public function down()
    {
        Schema::table('user_lesson_statuses', function (Blueprint $table) {
            $table->dropColumn('reschedule_to'); // date型カラムを削除
        });

        Schema::table('user_lesson_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('reschedule_to')->nullable(); // 元に戻す（BigInt想定）
        });
    }
}
