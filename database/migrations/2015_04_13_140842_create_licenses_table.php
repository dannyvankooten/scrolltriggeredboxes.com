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
			$table->integer('site_limit')->unsigned()->default(1);
			$table->dateTime('expires_at');
			$table->integer('sendowl_order_id')->nullable();
			$table->integer('user_id')->unsigned();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
