<?php

namespace App\Console\Commands;

use App\Services\Payments\StripeAgent;
use Illuminate\Console\Command;
use Stripe;

class StripeCancelSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:cancel-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels all subscriptions in the Stripe account.';

    /**
     * @var StripeAgent
     */
    protected $agent;

    /**
     * Create a new command instance.
     * @param StripeAgent $agent
     */
    public function __construct( StripeAgent $agent )
    {
        $this->agent = $agent;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subscriptions = Stripe\Subscription::all([ 'limit' => 100 ]);
        $count = count($subscriptions->data);
        $this->info(sprintf('%s subscriptions found.', $count));

        // ask for confirmation before proceeding
        $this->confirm(sprintf('Are you sure you want to cancel all %d subscriptions?',$count));

        foreach( $subscriptions->data as $subscription ) {
            $this->info(sprintf('Cancelling subscription %s', $subscription->id));
            $subscription->cancel();
        }
    }
}
