<?php namespace App\Console;

use App\Console\Commands\CreateInvoices;
use App\Console\Commands\EmailsUpcomingPayment;
use App\Console\Commands\StripeCancelSubscriptions;
use App\Console\Commands\StripeCreatePlans;
use App\Console\Commands\StripePollEvents;
use App\Console\Commands\StripeMigrateSubscriptions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		CreateInvoices::class,
        StripeMigrateSubscriptions::class,
        StripePollEvents::class,
        StripeCreatePlans::class,
        StripeCancelSubscriptions::class,
        EmailsUpcomingPayment::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{

	}

}
