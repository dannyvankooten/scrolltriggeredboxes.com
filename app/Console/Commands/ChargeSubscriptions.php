<?php

namespace App\Console\Commands;

use App\License;
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
        $today = new DateTime('today 00:00:00');
        $subscriptions = Subscription::where('next_charge_at', '<', $today)
            ->where('active', 1)
            ->with(['license', 'user'])
            ->get();


        // charge subscriptions
        foreach( $subscriptions as $subscription ) {

            // charge
            try {
                $success = $this->charger->subscription( $subscription );
            } catch( Exception $e ) {
                $this->error( sprintf( 'Charge for subscription #%d failed because of error: %s', $subscription->id, $e->getMessage() ) );
                continue;
            }

            // print some info
            $this->info("Successfully renewed license #{$subscription->license->id}");
        }

    }
}
