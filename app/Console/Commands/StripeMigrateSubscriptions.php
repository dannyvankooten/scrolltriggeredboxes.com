<?php

namespace App\Console\Commands;

use App\Services\Payments\StripeAgent;
use App\Subscription;
use Illuminate\Console\Command;

class StripeMigrateSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:migrate-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all local Subscriptions to Stripe';

    /**
     * @var StripeAgent
     */
    protected $agent;

    /**
     * Create a new command instance.
     *
     * @param StripeAgent $agent
     */
    public function __construct( StripeAgent $agent )
    {
        parent::__construct();

        $this->agent = $agent;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subscriptions = Subscription::where('active', 1)
            ->with(['license', 'user'])
            ->orderBy('next_charge_at', 'desc') // start with new ones as they are most likely to succeed
            ->get();

        foreach( $subscriptions as $subscription ) {
            $this->migrate( $subscription );
        }
    }

    protected function migrate( Subscription $subscription )
    {
        $license = $subscription->license;
        $user = $subscription->user;

        // set plan interval
        $license->interval = $subscription->interval;

        // let's go
        $this->info( sprintf( 'Migrating subscription %d, license %d for user %s', $subscription->id, $license->id, $user->email ) );

        try {
            $this->agent->resumeSubscription($license);
        } catch( \Exception $e ) {
            $this->warn( sprintf( "Error: %s", $e->getMessage() ) );
            return;
        }

        // up license limit so it fits in new plans
        if( $license->getPlan() === 'personal' && $license->site_limit < 2 ) {
            $license->site_limit = 2;
        }

        if( $license->getPlan() === 'developer' && $license->site_limit < 10 ) {
            $license->site_limit = 10;
        }

        // save changes
        $license->save();

        // delete subscription
        $subscription->delete();
    }
}
