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
            $table->float('diem_tien', 18, 2);
            $table->integer('type');
            $table->float('sales', 18, 2)->nullable();
            $table->float('fee', 18, 2);
            $table->string('win')->nullable();
            $table->integer('win_num')->nullable();
            $table->float('profit', 18, 2)->nullable();
            $table->boolean('status')->default(0)->comment('0: chưa tính kết quả, 1: đã tính kết quả');
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
