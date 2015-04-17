<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePluginsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('plugins', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->string('slug')->unique();
			$table->string('url')->unique();
			$table->string('version');
			$table->string('author')->default('');
			$table->text('changelog')->default('');
			$table->text('description')->default('');
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
		Schema::drop('plugins');
	}

}
