<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Plugin, App\Plan;
use App\License, App\User;

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

		$this->createPlugins();
		$this->createPlans();
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
			'short_description' => 'A beautiful set of eye-catching themes for your boxes.',
			'version' => '1.0',
			'author' => 'Danny van Kooten',
			'image_path' => '/images/plugins/theme-pack.jpg',
			'type' => 'premium'
		]);

		Plugin::create([
			'name' => 'MailChimp Sign-Up',
			'slug' => 'mailchimp-for-wp',
			'url' => 'mailchimp',
			'version' => '1.2',
			'changelog' => 'Changelog text',
			'short_description' => 'Sign-up forms for your MailChimp list, with ease.',
			'description' => 'MailChimp for WordPress allows you to place a sign-up form in your boxes.',
			'author' => 'Danny van Kooten',
			'image_path' => '/images/plugins/mailchimp.jpg',
			'external_url' => 'https://wordpress.org/plugins/mailchimp-for-wp/',
			'type' => 'free'
		]);

		Plugin::create([
			'name' => 'Related Posts',
			'slug' => 'related-posts-for-wp',
			'url' => 'related-posts',
			'version' => '1.3',
			'changelog' => 'Changelog text',
			'description' => 'Rated Posts for WordPress allows you to show visitors a highly related post once they are done reading the current one. A perfect way to decrease your bounce rate.',
			'short_description' => 'Ask visitors to read a related post when they\'re done reading.',
			'author' => 'Danny van Kooten',
			'image_path' => '/images/plugins/related-posts.jpg',
			'external_url' => 'https://relatedpostsforwp.com/',
			'type' => 'free'
		]);

		$this->command->info('plugins table seeded!');
	}

	// create sample sale for 1st sendowl product
	public function createLicenses() {
		$plugins = Plugin::all();
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
			'plan_id' => 1
		]);
		$license->user()->associate($user);
		$license->save();

		// grant access to all plugins
		$license->plugins()->attach( $plugins->lists('id') );

		$this->command->info('licenses table seeded!');
	}

	public function createPlans() {
		$plugins = Plugin::all();

		// personal license
		$plan_1 = new Plan([
			'name' => "Personal License",
			'site_limit' => 1,
			'sendowl_product_id' => 1
		]);
		$plan_1->save();

		// developer license
		$plan_2 = new Plan([
			'name' => "Developer License",
			'site_limit' => 10,
			'sendowl_product_id' => 2
		]);
		$plan_2->save();

		// grant access to all plugins
		$plan_1->plugins()->attach( $plugins->lists('id') );
		$plan_2->plugins()->attach( $plugins->lists('id') );

		$this->command->info('plans table seeded!');
	}

}
