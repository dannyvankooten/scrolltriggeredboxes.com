<?php

namespace App\Console\Commands;

use App\Events\SubscriptionChargeFailed;
use App\Services\Charger;
use App\Subscription;
use Illuminate\Console\Command;
use DateTime;
use Exception;

class ChargeSubscriptions extends Command
{

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
     * Create a new command instance.
     *
     * @param Charger $charger
     */
    public function __construct( Charger $charger )
    {
        parent::__construct();

        $this->charger = $charger;
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
            ->get();

        if( empty( $subscriptions ) ) {
            $this->info( 'No subscriptions with a payment due.' );
        }

        // charge subscriptions
        foreach( $subscriptions as $subscription ) {

            /** @var Subscription $subscription */
            if( ! $subscription->shouldTryPayment( $todayEnd ) ) {
                continue;
            }

            // charge
            try {
                $success = $this->charger->subscription( $subscription );
            } catch( Exception $e ) {
                $this->error( sprintf( 'Charge for subscription #%d failed because of error: %s', $subscription->id, $e->getMessage() ) );
                event(new SubscriptionChargeFailed($subscription));
                continue;
            }

            // print some info
            $this->info("Successfully renewed license #{$subscription->license->id}");
        }

    }
}
