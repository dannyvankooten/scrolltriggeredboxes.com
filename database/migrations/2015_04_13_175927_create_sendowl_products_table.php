<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendowlProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sendowl_products', function(Blueprint $table)
		{
			$table->integer('id')->unsigned();
			$table->string('name');
			$table->integer('site_limit')->default(1);
			$table->integer('plugin_id')->unsigned();
			$table->timestamps();

			$table->primary('id');
			$table->foreign('plugin_id')
			      ->references('id')->on('plugins')
			      ->onDelete('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sendowl_products');
	}

}
