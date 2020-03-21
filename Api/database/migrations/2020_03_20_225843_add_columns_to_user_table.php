<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUserTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('lo_rate')->default(21750)->after('key');
            $table->float('de_rate')->default(0.73)->after('lo_rate');
            $table->float('xien_rate')->default(0.62)->after('de_rate');
            $table->float('bacang_rate')->default(0.75)->after('xien_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->drop(['lo_rate', 'de_rate', 'xien_rate', 'bacang_rate']);
        });
    }
}
