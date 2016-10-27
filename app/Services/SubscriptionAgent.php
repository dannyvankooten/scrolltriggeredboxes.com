<?php

namespace App\Services;

use App\User;
use App\License;
use Exception;
use Stripe;
use Illuminate\Contracts\Logging\Log;

class SubscriptionAgent {

    /**
     * @var Log
     */
    protected $logger;

    /**
     * Charger constructor.
     *
     * @param string $stripeSecret
     * @param Log $logger
     */
    public function __construct( $stripeSecret, Log $logger ) {
        Stripe\Stripe::setApiKey( $stripeSecret );
        $this->logger = $logger;
    }

    /**
     * @param License $license
     * @return string
     */
    protected function getPlanId( License $license ) {
        return sprintf('boxzilla-%s-%sly', $license->getPlan(), $license->interval);
    }

    /**
     * @param License $license
     * @throws Exception
     */
    public function create( License $license ) {

        if( empty( $license->user->stripe_customer_id ) ) {
            throw new Exception( "User has no valid payment method registered." );
        }

        $subscription = Stripe\Subscription::create([
            'customer' => $license->user->stripe_customer_id,
            'plan' => $this->getPlanId( $license )
        ]);

        $license->stripe_subscription_id = $subscription->id;
        $license->extend();
    }

    /**
     * @param License $license
     */
    public function cancel( License $license ) {
        $subscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
        $subscription->cancel();
    }

    /**
     * @param License $license
     * @throws Exception
     */
    public function resume( License $license ) {

        if( empty( $license->user->stripe_customer_id ) ) {
            throw new Exception( "User has no valid payment method registered." );
        }

        // if license is expired, create a new subscription
        if( $license->isExpired() ) {
            return $this->create($license);
        }

        // create subscription but do not charge until license expiration date
        $subscription = Stripe\Subscription::create([
            'customer' => $license->user->stripe_customer_id,
            'plan' => $this->getPlanId($license),
            'trial_end' => $license->expires_at->getTimestamp(),
        ]);

        $license->stripe_subscription_id = $subscription->id;
    }

    public function updateNextChargeDate( License $license ) {
        $subscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
        $subscription->trial_end = $license->expires_at;
        $subscription->save();
    }

}