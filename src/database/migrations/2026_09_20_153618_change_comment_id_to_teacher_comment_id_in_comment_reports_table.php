<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comment_reports', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
        });

        Schema::table('comment_reports', function (Blueprint $table) {
            $table->renameColumn(
                'comment_id',
                'teacher_comment_id'
            );
        });

        Schema::table('comment_reports', function (Blueprint $table) {
            $table->foreign('teacher_comment_id')
                ->references('id')
                ->on('teacher_comments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comment_reports', function (Blueprint $table) {
            $table->dropForeign(['teacher_comment_id']);
        });

        Schema::table('comment_reports', function (Blueprint $table) {
            $table->renameColumn(
                'teacher_comment_id',
                'comment_id'
            );
        });

        Schema::table('comment_reports', function (Blueprint $table) {
            $table->foreign('comment_id')
                ->references('id')
                ->on('teacher_comments')
                ->cascadeOnDelete();
        });
        
    }
};
