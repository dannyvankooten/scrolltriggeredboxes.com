<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activations', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->integer('license_id')->unsigned();
			$table->integer('plugin_id')->unsigned();
			$table->string('domain');
			$table->string('url')->default('');
			$table->timestamps();

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
		Schema::drop('activations');
	}

}
