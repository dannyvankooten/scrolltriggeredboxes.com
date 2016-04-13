<?php

namespace App\Services;

use App\Subscription;
use App\License;

use Stripe\Stripe;
use DateTime;
use Exception;

class Charger {

    /**
     * Charger constructor.
     */
    public function __construct() {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @param Subscription $subscription
     *
     * @return bool
     * 
     * @throws Exception
     */
    public function subscription( Subscription $subscription ) {
        $user = $subscription->user;
        $today = new DateTime("now");
        $intervalString = "+1 {$subscription->interval}";

        // calculate amount in cents
        $amountInCents = $subscription->getAmountInclVat() * 100;

        try {
            $charge = \Stripe\Charge::create([
                "amount" => $amountInCents,
                "currency" => "USD",
                "customer" => $user->stripe_customer_id
            ]);
        } catch(\Stripe\Error\Card $e) {
           throw new Exception( $e->getMessage(), $e->getCode() );
        }

        // success! extend license
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