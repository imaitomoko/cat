<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lesson_values', function (Blueprint $table) {
            // lesson_value1 と lesson_value2 を削除
            $table->dropColumn(['lesson_value1', 'lesson_value2']);
            
            // 新しい lesson_value カラムを追加
            $table->string('lesson_value', 10)->nullable(); 
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_values', function (Blueprint $table) {
            // lesson_value カラムを削除
            $table->dropColumn('lesson_value');
            
            // lesson_value1 と lesson_value2 を元に戻す
            $table->string('lesson_value1', 10)->default('①'); // day1 に対応する値
            $table->string('lesson_value2', 10)->nullable(); // day2 に対応する値
            //
        });
    }
};
