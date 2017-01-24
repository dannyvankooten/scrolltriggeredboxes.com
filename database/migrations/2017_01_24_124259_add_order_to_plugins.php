<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderToPlugins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->smallInteger('order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
