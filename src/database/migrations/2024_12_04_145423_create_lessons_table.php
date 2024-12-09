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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_id', 10)->unique();
            $table->integer('year');
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->string('day1', 10);
            $table->time('start_time1');
            $table->integer('duration1');
            $table->string('day2', 10)->nullable();
            $table->time('start_time2')->nullable();
            $table->integer('duration2')->nullable();
            $table->integer('max_number');
            $table->string('lesson_value', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
