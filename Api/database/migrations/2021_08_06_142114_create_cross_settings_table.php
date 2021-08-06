<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrossSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cross_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('lo')->default(0);
            $table->integer('xien2')->default(0);
            $table->integer('xien3')->default(0);
            $table->integer('xien4')->default(0);
            $table->integer('de')->default(0);
            $table->integer('bacang')->default(0);
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
        Schema::dropIfExists('cross_settings');
    }
}
