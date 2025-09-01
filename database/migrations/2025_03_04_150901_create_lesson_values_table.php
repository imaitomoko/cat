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
        Schema::create('lesson_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete(); // lessons テーブルと関連付け
            $table->date('date');  // レッスンの日付
            $table->string('lesson_value1', 10)->default('①'); // day1 に対応する値
            $table->string('lesson_value2', 10)->nullable(); // day2 に対応する値
            $table->timestamps();

            // 同じ lesson_id で同じ日付のデータが重複しないようにする
            $table->unique(['lesson_id', 'date']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_values', function (Blueprint $table) {
            //
        });
    }
};
