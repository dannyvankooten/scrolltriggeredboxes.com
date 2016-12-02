<?php

namespace App\Console\Commands;

use App\Services\Payments\Gateways\StripeGateway;
use App\Services\Payments\PaymentException;
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
     * @var StripeGateway
     */
    protected $agent;

    /**
     * Create a new command instance.
     *
     * @param StripeGateway $stripeGateway
     */
    public function __construct( StripeGateway $stripeGateway )
    {
        parent::__construct();

        $this->agent = $stripeGateway;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $licenses = License::with(['subscription', 'user'])->get();
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
        // first, migrate license to new plan.
        if( $license->getPlan() === 'personal' && $license->site_limit < 2 ) {
            $this->line(sprintf("License %d: Upping site limit to 2", $license->id));
            $license->site_limit = 2;
        }

        if( $license->getPlan() === 'developer' && $license->site_limit < 10 ) {
            $this->line(sprintf("License %d: Upping site limit to 10", $license->id));
            $license->site_limit = 10;
        }

        // if license has subscription, migrate that.
        if( $license->subscription ) {
            $this->migrateSubscription($license, $license->subscription);
        }

        // save changes
        $license->save();
    }

    /**
     * @param License $license
     * @param Subscription $subscription
     */
    protected function migrateSubscription(License $license, Subscription $subscription)
    {
        // make sure license has no stripe subscription yet.
        if( ! empty( $license->stripe_subscription_id ) ) {
            $this->line(sprintf('License %d: Subscription already exists', $license->id));
            return;
        }

        // set plan interval
        $license->status = 'inactive'; // start with inactive status
        $license->interval = $subscription->interval;

        if( $subscription->active ) {
            $this->line(sprintf('License %d: Creating Stripe subscription', $license->id));

            try {
                $this->agent->createSubscription($license);
            } catch( PaymentException $e ) {
                $this->warn( sprintf( "License %d: Error creating Stripe subscription - %s", $license->id, $e->getMessage() ) );
                return;
            }

            $this->info(sprintf('License %d: Successfully migrated subscription!', $license->id));
        }

        // delete subscription
        //$subscription->delete();
    }
}
