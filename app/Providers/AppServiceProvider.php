<?php namespace App\Providers;

use App\Services\Payments\Cashier;
use App\Services\Payments\StripeAgent;
use App\Services\Invoicer\Moneybird;
use App\Services\Purchaser;
use App\Services\TaxRateResolver;
use HelpScoutApp\DynamicApp;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\ServiceProvider;

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

        $this->app->singleton( Cashier::class, function ($app) {
            $log = $app[Log::class];
            return new Cashier( $log );
        });

		$this->app->singleton( StripeAgent::class, function ($app) {
			$stripeSecret = config('services.stripe.secret');
            $cashier = $app[Cashier::class];
            $log = $app[Log::class];
			return new StripeAgent( $stripeSecret, $cashier, $log );
		});

		$this->app->singleton( Purchaser::class, function ($app) {
            $stripeAgent = $app[StripeAgent::class];
			return new Purchaser($stripeAgent);
		});

	}

}
