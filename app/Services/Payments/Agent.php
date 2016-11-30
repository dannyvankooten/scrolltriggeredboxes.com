<?php

namespace App\Services\Payments;

use App\License;
use App\Payment;

class Agent {

    /**
     * Agent constructor.
     *
     * @param StripeAgent $stripe
     * @param PayPalAgent $paypal
     */
    public function __construct( StripeAgent $stripe, PayPalAgent $paypal ) {
        $this->stripe = $stripe;
        $this->paypal = $paypal;
    }

    /**
     * @param License $license
     * @return string
     */
    public function createSubscription( License $license ) {
        return $this->{$license->payment_method}->createSubscription($license);
    }

    /**
     * @param License $license
     * @param string $token
     */
    public function startSubscription( License $license, $token = '' ) {
        return $this->{$license->payment_method}->startSubscription($license, $token);
    }

    /**
     * @param License $license
     */
    public function cancelSubscription( License $license ) {
        return $this->{$license->payment_method}->cancelSubscription($license);
    }

    /**
     * @param License $license
     */
    public function updateNextChargeDate( License $license ) {
        return $this->{$license->payment_method}->updateNextChargeDate($license);
    }


}