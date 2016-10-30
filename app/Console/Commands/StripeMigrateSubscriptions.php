<?php

namespace App\Console\Commands;

use App\Services\Payments\PaymentException;
use App\Services\Payments\StripeAgent;
use App\Subscription;
use App\License;

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
        $licenses = License::with(['subscription', 'user'])->all();

        $this->info(sprintf('%d licenses found.', count($licenses)));

        foreach( $licenses as $license ) {
            $this->migrateLicense($license);
        }
    }

    /**
     * @param License $license
     */
    protected function migrateLicense(License $license)
    {
        $this->info(sprintf('Migrating license %d for user %s', $license->id, $license->user->email));

        // first, migrate license to new plan.
        if( $license->getPlan() === 'personal' && $license->site_limit < 2 ) {
            $this->info(sprintf("Upping site limit for license %d to 2", $license->id));
            $license->site_limit = 2;
        }

        if( $license->getPlan() === 'developer' && $license->site_limit < 10 ) {
            $this->info(sprintf("Upping site limit for license %d to 10", $license->id));
            $license->site_limit = 10;
        }

        // if license has subscription, migrate that.
        if($license->subscription) {
            $this->migrateSubscription($license->subscription);
        }

        // save changes
        $license->save();
    }

    /**
     * @param Subscription $subscription
     */
    protected function migrateSubscription(Subscription $subscription)
    {
        $license = $subscription->license;
        $user = $subscription->user;

        $this->info(sprintf("Migrating subscription for license %d", $license->id));


        // make sure license has no stripe subscription yet.
        if( ! empty( $license->stripe_subscription_id ) ) {
            $this->info(sprintf('Skipping subscription %d as license already has attached Stripe subscription.', $subscription->id));
            return;
        }

        // let's go
        $this->info( sprintf( 'Migrating subscription %d for user %s', $subscription->id, $user->email ) );

        // set plan interval
        $license->status = 'inactive'; // start with inactive status
        $license->interval = $subscription->interval;

        if( $subscription->active ) {
            try {
                $this->agent->createSubscription($license);
            } catch( PaymentException $e ) {
                $this->warn( sprintf( "Error creating Stripe subscription: %s", $e->getMessage() ) );
                return;
            }
        }

        $this->info(sprintf('Success! Deleting local subscription %d.', $subscription->id));

        // delete subscription
        $subscription->delete();
    }
}
