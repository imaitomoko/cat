<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReschedulesTableForNewStructure extends Migration
{
    public function up(): void
    {
        Schema::table('reschedules', function (Blueprint $table) {
            // 既存の new_user_lesson_id 外部キー削除
            $table->dropForeign(['new_user_lesson_id']);
            $table->dropColumn('new_user_lesson_id');

            // 新たなカラム追加
            $table->foreignId('lesson_id')->after('user_lesson_status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->after('lesson_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reschedules', function (Blueprint $table) {
            // 新しく追加したカラム削除
            $table->dropForeign(['lesson_id']);
            $table->dropColumn('lesson_id');

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // 元に戻す（必要であれば）
            $table->foreignId('new_user_lesson_id')->constrained('user_lessons')->cascadeOnDelete();
        });
    }
}
