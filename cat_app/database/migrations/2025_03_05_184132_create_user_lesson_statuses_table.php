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
        Schema::create('user_lesson_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_lesson_id')->constrained()->cascadeOnDelete();
            $table->date('date'); // 日付ごとのステータス
            $table->string('status', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lesson_statuses');
    }
};
