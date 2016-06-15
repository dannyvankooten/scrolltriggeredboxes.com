<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePluginLicensesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('plugin_licenses', function(Blueprint $table)
		{
			$table->integer('license_id')->unsigned();
			$table->integer('plugin_id')->unsigned();

			$table->primary(['license_id', 'plugin_id']);
			$table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
			$table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('plugin_licenses');
	}

}
