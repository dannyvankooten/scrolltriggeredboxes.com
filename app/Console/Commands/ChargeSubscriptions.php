<?php

namespace App\Console\Commands;

use App\License;
use App\Subscription;
use Illuminate\Console\Command;
use DateTime;
use Stripe\Stripe;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
        $subscriptions = Subscription::where('next_charge_at', '<', $today)->get();

        // charge user
        Stripe::setApiKey(config('services.stripe.secret'));

        // charge subscriptions
        foreach( $subscriptions as $subscription ) {

            try {
                $charge = \Stripe\Charge::create([
                    "amount" => $subscription->amount * 100, // amount in cents
                    "currency" => "USD",
                    "customer" => $subscription->user->stripe_customer_id
                ]);
            } catch(\Stripe\Error\Card $e) {
                // The card has been declined
                $this->error($e);

                // TODO: Send out email to user that their subscription renewal failed
                continue;
            }

            // success! extend license
            $today = new DateTime("now");
            $intervalString = "+1 {$subscription->interval}";

            /** @var License $license */
            $license = $subscription->license;

            // start counting at expiration date or from today if already expired
            $license->expires_at = $license->isExpired() ? $today->modify( $intervalString ) : $license->expires_at->modify( $intervalString );
            $license->save();

            // set new charge date
            $subscription->next_charge_at = $license->expires_at->modify('-1 week');
            $subscription->save();

            // print some info
            $this->info("Successfully renewed license #{$license->id}");
        }

    }
}
