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
            $table->float('lo_rate', 18, 2);
            $table->integer('lo_percent')->default(80000);
            $table->float('de_rate', 18, 2);
            $table->integer('de_percent');
            $table->float('xien_rate', 18, 2);
            $table->integer('xien2_percent')->default(10);
            $table->integer('xien3_percent')->default(40);
            $table->integer('xien4_percent')->default(100);
            $table->float('bacang_rate', 18, 2);
            $table->integer('bacang_percent')->default(400);
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
