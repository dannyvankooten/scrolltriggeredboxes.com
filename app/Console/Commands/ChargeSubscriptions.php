<?php

namespace App\Console\Commands;

use App\Events\SubscriptionChargeFailed;
use App\Jobs\EmailFailedChargeNotification;
use App\Services\Payments\Charger;
use App\Services\Payments\PaymentException;
use App\Subscription;
use Illuminate\Console\Command;
use DateTime;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ChargeSubscriptions extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge all subscriptions with a due payment.';

    /**
     * @var Charger
     */
    protected $charger;

    /**
     * @var Log
     */
    protected $log;

    /**
     * Create a new command instance.
     *
     * @param Charger $charger
     */
    public function __construct( Charger $charger, Log $log )
    {
        parent::__construct();

        $this->charger = $charger;
        $this->log = $log;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // find all subscriptions with a due payment
        $todayEnd = new DateTime('today 23:59:59');

        $subscriptions = Subscription::where('next_charge_at', '<', $todayEnd)
            ->where('active', 1)
            ->with(['license', 'user'])
            ->orderBy('next_charge_at', 'desc') // start with new ones as they are most likely to succeed
            ->get();

        if( empty( $subscriptions ) ) {
            $this->info( 'No subscriptions with a payment due.' );
        }

        // charge subscriptions
        foreach( $subscriptions as $subscription ) {

            /** @var Subscription $subscription */
            // should we charge this subscription today?
            if( ! $this->shouldCharge( $subscription ) ) {
                $this->info( sprintf( 'Skipping subscription %d. ', $subscription->id ) );
                continue;
            }

            // is subscription even chargeable?
            if( ! $this->charger->chargeable( $subscription ) ) {
                // TODO: Warn user about this
                $message = sprintf( 'No valid payment method registered for subscription %d for user %s.', $subscription->id, $subscription->user->email );
                $this->log->warning( $message );
                $this->warn( $message );
                continue;
            }

            // charge
            try {
                $success = $this->charger->subscription( $subscription );
            } catch( PaymentException $e ) {
                $message = sprintf( 'Charge for subscription #%d failed because of error: %s', $subscription->id, $e->getMessage() );
                $this->log->error( $message );
                $this->error( $message );
                $this->dispatch((new EmailFailedChargeNotification($subscription)));
                continue;
            }

            // print some info
            $message = "Successfully renewed license #{$subscription->license->id} for user {$subscription->user->email}.";
            $this->log->info($message);
            $this->info($message);
        }
    }

    /**
     * @param Subscription $subscription
     * 
     * @return bool
     */
    public function shouldCharge( Subscription $subscription ) {
        return $subscription->next_charge_at->isToday() || ( $subscription->next_charge_at->diffInDays() % 3 ) === 0;
    }
}
