<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lov1')->default('18:09');
            $table->string('lov2')->default('18:10');
            $table->string('dev1')->default('18:19');
            $table->string('dev2')->default('18:20');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_settings');
    }
}
