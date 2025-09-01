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
        Schema::create('reschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_lesson_status_id')->constrained('user_lesson_statuses')->cascadeOnDelete();
            $table->foreignId('new_user_lesson_id')->constrained('user_lessons')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reschedules', function (Blueprint $table) {
            //
        });
    }
};
