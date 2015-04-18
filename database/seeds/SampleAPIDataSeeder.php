<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Plugin, App\SendowlProduct;
use App\License, App\Site, App\User;

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
		DB::table('licenses')->delete();
		DB::table('plugins')->delete();
		DB::table('plugin_licenses')->delete();
		DB::table('sendowl_products')->delete();
		DB::table('activations')->delete();

		$this->createPlugins();
		$this->createSendowlProducts();
		$this->createLicenses();

	}

	public function createPlugins() {
		// create plugins
		Plugin::create([
			'name' => 'Theme Pack',
			'slug' => 'stb-theme-pack',
			'url' => 'theme-pack',
			'changelog' => 'Changelog text',
			'description' => 'Description for Theme Pack.',
			'version' => '1.0',
			'author' => 'Danny van Kooten'
		]);

		Plugin::create([
			'name' => 'MailChimp Sign-Up',
			'slug' => 'mailchimp-for-wp',
			'url' => 'mailchimp',
			'version' => '1.2',
			'changelog' => 'Changelog text',
			'description' => 'Description for MailChimp for WordPress.',
			'author' => 'Danny van Kooten'
		]);

		Plugin::create([
			'name' => 'Related Posts',
			'slug' => 'related-posts-for-wp',
			'url' => 'related-posts',
			'version' => '1.3',
			'changelog' => 'Changelog text',
			'description' => 'Description for Related Posts for WordPress.',
			'author' => 'Danny van Kooten'
		]);

		$this->command->info('plugins table seeded!');
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
			'site_limit' => 2,
			'expires_at' => new \DateTime('+1 year'),
			'sendowl_order_id' => 100,
		]);
		$license->user()->associate($user);

		$license->save();

		// grant access to all plugins
		foreach( SendowlProduct::all() as $product ) {
			$license->grantAccessTo( $product->plugin );
		}



		$this->command->info('licenses table seeded!');
	}

	public function createSendowlProducts() {
		$plugins = Plugin::all();

		// create two SendOwl products for each plugin
		$id = 0;
		foreach( $plugins as $plugin ) {

			$id++;

			$product = new SendowlProduct([
				'id' => $id,
				'name' => "STB Theme Pack (single)",
				'plugin_id' => $plugin->id,
				'site_limit' => 1
			]);
			$product->save();

			$id++;

			$product = new SendowlProduct([
				'id' => $id,
				'name' => "STB Theme Pack (developer)",
				'plugin_id' => $plugin->id,
				'site_limit' => 10
			]);
			$product->save();

		}

		$this->command->info('sendowl_products table seeded!');
	}

}
