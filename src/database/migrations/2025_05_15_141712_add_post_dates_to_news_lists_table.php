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
        Schema::table('news_lists', function (Blueprint $table) {
            $table->date('post_date')->nullable()->after('news_list'); 
            $table->date('end_date')->nullable()->after('post_date'); 
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_lists', function (Blueprint $table) {
            $table->dropColumn(['post_date', 'end_date']);
            //
        });
    }
};
