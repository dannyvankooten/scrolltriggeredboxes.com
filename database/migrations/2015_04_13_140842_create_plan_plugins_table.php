<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanPluginsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('plan_plugins', function(Blueprint $table)
		{
			$table->integer('plan_id')->unsigned();
			$table->integer('plugin_id')->unsigned();
			$table->primary( [ 'plan_id', 'plugin_id' ] );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('plan_plugins');
	}

}

