<?php namespace App\Providers;


use App\Services\Payments\PayPalEvent;
use App;
use Braintree;
use Stripe;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		Illuminate\Auth\Events\Login::class => [
			App\Listeners\UserLoginHandler::class,
		],
        Stripe\Event::class => [
            App\Listeners\StripeEventHandler::class,
        ],
        Braintree\WebhookNotification::class => [
            App\Listeners\BraintreeEventHandler::class,
        ]
	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);

		//
	}

}
