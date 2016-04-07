<?php namespace App\Providers;

use App\VatValidator;
use HelpScoutApp\DynamicApp;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Validator::extend('vat_number', function($attribute, $value, $parameters, $validator ) {
			$vatValidator = new VatValidator();
			$data = $validator->getData();
			$country = isset( $data['country'] ) ? $data['country'] : '';
			return $vatValidator->check( $value, $country );
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(DynamicApp::class, function ($app) {
			return new DynamicApp( config('services.helpscout')['secret'] );
		});

		$this->app->singleton( \VatValidator::class, function($app) {
			return new VatValidator();
		});
	}

}
