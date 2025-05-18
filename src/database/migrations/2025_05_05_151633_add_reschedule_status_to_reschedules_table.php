<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRescheduleStatusToReschedulesTable extends Migration
{
    public function up()
    {
        Schema::table('reschedules', function (Blueprint $table) {
            $table->string('reschedule_status')->default('未受講')->after('lesson_id');
        });
    }

    public function down()
    {
        Schema::table('reschedules', function (Blueprint $table) {
            $table->dropColumn('reschedule_status');
        });
    }
}
