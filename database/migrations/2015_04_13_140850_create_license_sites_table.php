<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLicenseSitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('license_sites', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('license_id')->unsigned();
			$table->string('url');
			$table->string('plugin');
			$table->boolean('active')->default(false);
			$table->timestamps();

			$table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('license_sites');
	}

}
