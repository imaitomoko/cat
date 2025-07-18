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
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('comments', 'class_id')) {
                $table->unsignedBigInteger('class_id')->nullable()->after('school_id');
            }
            if (!Schema::hasColumn('comments', 'year')) {
                $table->integer('year')->nullable()->after('class_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // 必要であれば削除チェックも追加
            if (Schema::hasColumn('comments', 'school_id')) {
                $table->dropColumn('school_id');
            }
            if (Schema::hasColumn('comments', 'class_id')) {
                $table->dropColumn('class_id');
            }
            if (Schema::hasColumn('comments', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
};
