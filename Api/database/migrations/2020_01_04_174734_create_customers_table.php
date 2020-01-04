<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('name');
            $table->integer('lo_rate');
            $table->integer('lo_percent')->nullable();
            $table->integer('de_rate');
            $table->integer('de_percent');
            $table->integer('xien_rate');
            $table->integer('xien2_percent')->nullable();;
            $table->integer('xien3_percent')->nullable();;
            $table->integer('xien4_percent')->nullable();;
            $table->integer('bacang_rate');
            $table->integer('bacang_percent')->nullable();;
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
        Schema::dropIfExists('customers');
    }
}
