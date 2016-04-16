<?php namespace App\Providers;

use HelpScoutApp\DynamicApp;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp;

use App\Services\Invoicer\Invoicer;

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
		$this->app->singleton( DynamicApp::class, function ($app) {
			return new DynamicApp( config('services.helpscout')['secret'] );
		});

		$this->app->singleton( Invoicer::class, function ($app) {
			$config = config('services.moneybird');
			return new Invoicer( $config['administration'], $config['token'] );
		});

	}

}
