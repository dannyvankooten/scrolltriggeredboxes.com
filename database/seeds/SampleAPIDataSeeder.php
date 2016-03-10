<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Plugin, App\Plan;
use App\License, App\User;
use Illuminate\Support\Facades\DB;

class SampleAPIDataSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		Model::unguard();

		DB::table('users')->delete();
		DB::table('plan_plugins')->delete();
		DB::table('licenses')->delete();
		DB::table('plugins')->delete();
		DB::table('plugin_licenses')->delete();
		DB::table('plans')->delete();
		DB::table('activations')->delete();


		$this->createPlans();
		$this->createLicenses();
	}


	// create sample sale for 1st sendowl product
	public function createLicenses() {


		$user = new User([
			'name' => 'Danny van Kooten',
			'email' => 'dannyvankooten@gmail.com',
			'password' => Hash::make('password')
		]);
		$user->save();

		// create license
		$license = new License([
			'license_key' => '4ELLX-E0BIW-BU0GP-94HW9',
			'site_limit' => 50,
			'expires_at' => new \DateTime('+1 year'),
			'sendowl_order_id' => 100,
			'plan_id' => 1
		]);
		$license->user()->associate($user);
		$license->save();

		$this->command->info('licenses table seeded!');
	}

	public function createPlans() {
		// personal license
		$plan_1 = new Plan([
			'id' => 1,
			'name' => "Personal License",
			'site_limit' => 1,
			'sendowl_product_id' => 169390
		]);
		$plan_1->save();

		// developer license
		$plan_2 = new Plan([
			'id' => 2,
			'name' => "Developer License",
			'site_limit' => 10,
			'sendowl_product_id' => 169391
		]);
		$plan_2->save();

		$this->command->info('plans table seeded!');
	}

}
