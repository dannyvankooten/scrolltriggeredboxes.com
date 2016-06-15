<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

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
			$table->text('short_description')->default('');
			$table->text('description')->default('');
			$table->string('type')->default('premium');
			$table->string('external_url')->default('');
			$table->string('image_path')->default('');
			$table->text('upgrade_notice')->nullable();
			$table->string('requires')->nullable();
			$table->string('tested')->nullable();
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
