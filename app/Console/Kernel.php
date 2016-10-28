<?php namespace App\Console;

use App\Console\Commands\CreateInvoices;
use App\Console\Commands\StripeCreatePlans;
use App\Console\Commands\StripePollInvoicePaymentFailures;
use App\Console\Commands\StripePollPaidInvoices;
use App\Console\Commands\StripePollRefundedCharges;
use App\Console\Commands\SubscriptionsMigrateToStripe;
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
        SubscriptionsMigrateToStripe::class,
        StripePollPaidInvoices::class,
        StripePollRefundedCharges::class,
        StripePollInvoicePaymentFailures::class,
        StripeCreatePlans::class,
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
