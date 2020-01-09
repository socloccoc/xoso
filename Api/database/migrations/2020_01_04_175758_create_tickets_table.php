<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('customer_daily_id');
            $table->string('chuoi_so');
            $table->float('diem_tien', 8, 2);
            $table->integer('type');
            $table->float('sales', 8, 2)->nullable();
            $table->float('fee', 8, 2);
            $table->string('win')->nullable();
            $table->integer('win_num')->nullable();
            $table->float('profit', 8, 2)->nullable();
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
        Schema::dropIfExists('tickets');
    }
}
