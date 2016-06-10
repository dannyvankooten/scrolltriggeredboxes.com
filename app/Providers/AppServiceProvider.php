<?php namespace App\Providers;

use App\Services\Payments\Charger;
use App\Services\Invoicer\Moneybird;
use App\Services\Purchaser;
use App\Services\TaxRateResolver;
use HelpScoutApp\DynamicApp;
use Illuminate\Contracts\Logging\Log;
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

		$this->app->singleton( TaxRateResolver::class, function($app) {
			return new TaxRateResolver();
		});

		$this->app->singleton( Invoicer::class, function ($app) {
			$config = config('services.moneybird');
			$moneybird = new Moneybird( $config['administration'], $config['token'] );

			$defaultCacheDriver = $app['cache']->getDefaultDriver();
			$cacheDriver = $app['cache']->driver( $defaultCacheDriver );

			return new Invoicer( $moneybird, $app[ TaxRateResolver::class ], $cacheDriver );
		});

		$this->app->singleton( Charger::class, function ($app) {
			$stripeSecret = config('services.stripe.secret');
			$log = $app[Log::class];
			return new Charger( $stripeSecret, $log );
		});

		$this->app->singleton( Purchaser::class, function ($app) {
			return new Purchaser( $app[Charger::class]);
		});

	}

}
