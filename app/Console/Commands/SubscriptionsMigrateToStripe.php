<?php

namespace App\Console\Commands;

use App\Services\SubscriptionAgent;
use App\Subscription;
use Illuminate\Console\Command;

class SubscriptionsMigrateToStripe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:migrate-to-stripe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all local Subscriptions to Stripe';

    /**
     * @var SubscriptionAgent
     */
    protected $agent;

    /**
     * Create a new command instance.
     *
     * @param SubscriptionAgent $agent
     */
    public function __construct( SubscriptionAgent $agent )
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

        $this->info( sprintf( 'Migrating subscription %d, license %d for user %s', $subscription->id, $license->id, $user->email ) );

        try {
            $this->agent->resume($license);
        } catch( \Exception $e ) {
            $this->warn( sprintf( "Error: %s", $e->getMessage() ) );
            return;
        }

        // save changes
        $license->save();

        // delete subscription
        $subscription->delete();
    }
}
