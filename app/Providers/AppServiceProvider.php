<?php namespace App\Providers;

use App\Services\Payments\Agent;
use App\Services\Payments\BraintreeAgent;
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

		$this->app->singleton( Invoicer::class, function ($app) {
			$config = config('services.moneybird');
			$moneybird = new Moneybird( $config['administration'], $config['token'] );
            $taxRateResolver = new TaxRateResolver();
			$defaultCacheDriver = $app['cache']->getDefaultDriver();
			$cacheDriver = $app['cache']->driver( $defaultCacheDriver );
            $log = $app[Log::class];

			return new Invoicer( $moneybird, $taxRateResolver, $cacheDriver, $log );
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

        $this->app->singleton( BraintreeAgent::class, function ($app) {
            \Braintree\Configuration::environment(config('services.braintree.environment'));
            \Braintree\Configuration::merchantId(config('services.braintree.merchant_id'));
            \Braintree\Configuration::publicKey(config('services.braintree.public_key'));
            \Braintree\Configuration::privateKey(config('services.braintree.private_key'));

            $cashier = $app[Cashier::class];
            $log = $app[Log::class];
            return new BraintreeAgent( $cashier, $log );
        });

        $this->app->singleton( Agent::class, function ($app) {
            return new Agent($app[StripeAgent::class], $app[BraintreeAgent::class]);
        });

		$this->app->singleton( Purchaser::class, function ($app) {
            $stripeAgent = $app[StripeAgent::class];
			return new Purchaser($stripeAgent);
		});




	}

}
