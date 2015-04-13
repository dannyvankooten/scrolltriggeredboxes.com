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
			$table->increments('id');
			$table->string('license_key');
			$table->string('email');
			$table->integer('sendowl_product_id')->nullable();
			$table->integer('sendowl_order_id')->nullable();
			$table->integer('site_limit')->default(1);
			$table->dateTime('expires_at');
			$table->timestamps();

			$table->unique('license_key');
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
