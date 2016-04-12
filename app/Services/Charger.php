<?php

namespace App\Services;

use App\Subscription;
use App\License;

use Stripe\Stripe;
use DateTime;
use Exception;

class Charger {

    public function __construct() {

    }

    /**
     * @param Subscription $subscription
     *
     * @return bool
     * 
     * @throws Exception
     */
    public function subscription( Subscription $subscription ) {
        // charge user
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $charge = \Stripe\Charge::create([
                "amount" => $subscription->amount * 100, // amount in cents
                "currency" => "USD",
                "customer" => $subscription->user->stripe_customer_id
            ]);
        } catch(\Stripe\Error\Card $e) {
           throw new Exception( $e->getMessage(), $e->getCode() );
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

        return true;
    }


}