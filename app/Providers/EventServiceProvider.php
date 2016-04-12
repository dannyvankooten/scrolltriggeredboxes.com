<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'App\Events\UserCreated' => [
			'App\Listeners\Events\EmailUserCredentials',
		],
		'Illuminate\Auth\Events\Login' => [
			'App\Listeners\UserLoginHandler',
		],
		'App\Events\LicenseCreated' => [
			'App\Listeners\Events\PushNewLicenseNotification',
		],
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
