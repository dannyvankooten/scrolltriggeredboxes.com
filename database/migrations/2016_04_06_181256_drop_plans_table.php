<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('licenses', function ($table) {
            $table->dropForeign('licenses_plan_id_foreign');
            $table->dropColumn('plan_id');
            $table->dropColumn('sendowl_order_id');
        });

        Schema::drop('plan_plugins');
        Schema::drop('plans');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('plans', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->integer('site_limit')->default(1);
            $table->integer('sendowl_product_id')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_plugins', function(Blueprint $table)
        {
            $table->integer('plan_id')->unsigned();
            $table->integer('plugin_id')->unsigned();
            $table->primary( [ 'plan_id', 'plugin_id' ] );
        });

        Schema::table('licenses', function ($table) {
            $table->integer('sendowl_order_id')->nullable();
            $table->integer('plan_id')->unsigned()->nullable();
            $table->foreign('plan_id')->references('id')->on('plans');
        });

    }
}
