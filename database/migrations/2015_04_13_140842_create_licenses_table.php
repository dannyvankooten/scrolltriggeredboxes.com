<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

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
			$table->timestamp('expires_at');
			$table->integer('sendowl_order_id')->nullable();
			$table->integer('user_id')->unsigned();
			$table->integer('plan_id')->unsigned()->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('plan_id')->references('id')->on('plans');
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
