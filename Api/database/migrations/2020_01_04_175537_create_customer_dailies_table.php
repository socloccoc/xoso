<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerDailiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_dailies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('daily_id');
            $table->integer('customer_id');
            $table->string('name');
            $table->string('money_in');
            $table->string('money_out')->nullable();
            $table->string('profit')->nullable();
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
        Schema::dropIfExists('customer_dailies');
    }
}
