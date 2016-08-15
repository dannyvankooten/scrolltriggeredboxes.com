<?php namespace App\Console;

use App\Console\Commands\ChargeSubscriptions;
use App\Console\Commands\CreateInvoices;
use App\Console\Commands\PayPal\CreatePlan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		ChargeSubscriptions::class,
		CreateInvoices::class,
        CreatePlan::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('subscriptions:charge')
				 ->dailyAt('05:00');
	}

}
