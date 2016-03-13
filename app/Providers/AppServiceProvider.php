<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(\HelpScoutApp\DynamicApp::class, function ($app) {
			return new \HelpScoutApp\DynamicApp( config('services.helpscout')['secret'] );
		});
	}

}
