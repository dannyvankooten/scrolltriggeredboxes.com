<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropAllTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('plan_plugins');
		Schema::dropIfExists('plugin_licenses');
		Schema::dropIfExists('plans');
		Schema::dropIfExists('activations');
		Schema::dropIfExists('licenses');
		Schema::dropIfExists('plugins');
		Schema::dropIfExists('users');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

	}

}
