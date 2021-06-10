<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummaryResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summary_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('date');
            $table->string('dac_biet');
            $table->string('nhat');
            $table->string('nhi_1');
            $table->string('nhi_2');
            $table->string('ba_1');
            $table->string('ba_2');
            $table->string('ba_3');
            $table->string('ba_4');
            $table->string('ba_5');
            $table->string('ba_6');
            $table->string('tu_1');
            $table->string('tu_2');
            $table->string('tu_3');
            $table->string('tu_4');
            $table->string('nam_1');
            $table->string('nam_2');
            $table->string('nam_3');
            $table->string('nam_4');
            $table->string('nam_5');
            $table->string('nam_6');
            $table->string('sau_1');
            $table->string('sau_2');
            $table->string('sau_3');
            $table->string('bay_1');
            $table->string('bay_2');
            $table->string('bay_3');
            $table->string('bay_4');
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
        Schema::dropIfExists('summary_results');
    }
}
