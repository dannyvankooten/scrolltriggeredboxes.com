<?php namespace App\Providers;

use App\Services\Payments\Agent;
use App\Services\Payments\Gateways\BraintreeGateway;
use App\Services\Payments\Gateways\StripeGateway;

use App\Services\Payments\Cashier;
use App\Services\Invoicer\Moneybird;
use App\Services\Purchaser;
use App\Services\TaxRateResolver;
use HelpScoutApp\DynamicApp;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Mail\Mailer;
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
            $mailer = $app[Mailer::class];
            $log = $app[Log::class];
            return new Cashier( $mailer, $log );
        });

		$this->app->singleton( StripeGateway::class, function ($app) {
			$stripeSecret = config('services.stripe.secret');
            $cashier = $app[Cashier::class];
            $log = $app[Log::class];
			return new StripeGateway( $stripeSecret, $cashier, $log );
		});

        $this->app->singleton( BraintreeGateway::class, function ($app) {
            \Braintree\Configuration::environment(config('services.braintree.environment'));
            \Braintree\Configuration::merchantId(config('services.braintree.merchant_id'));
            \Braintree\Configuration::publicKey(config('services.braintree.public_key'));
            \Braintree\Configuration::privateKey(config('services.braintree.private_key'));
            $cashier = $app[Cashier::class];
            $log = $app[Log::class];
            return new BraintreeGateway( $cashier, $log );
        });

        $this->app->singleton( Agent::class, function ($app) {
            return new Agent($app[StripeGateway::class], $app[BraintreeGateway::class]);
        });

		$this->app->singleton( Purchaser::class, function($app) {
			return new Purchaser();
		});


	}

}
