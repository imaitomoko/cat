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
        Schema::create('send_to', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained('mails')->cascadeOnDelete();
            $table->foreignId('user_lesson_id')->constrained('user_lessons')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_to');
    }
};
