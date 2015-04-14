<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLicensesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('licenses', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('license_key')->unique();
			$table->string('email');
			$table->integer('site_limit')->unsigned()->default(1);
			$table->dateTime('expires_at');
			$table->integer('sendowl_order_id')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('licenses');
	}

}
