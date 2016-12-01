<?php

namespace App\Services\Payments;

use App\License;
use App\Payment;

class Agent {

    private $stripe;
    private $braintree;

    /**
     * Agent constructor.
     *
     * @param StripeAgent $stripe
     * @param BraintreeAgent $braintree
     */
    public function __construct( StripeAgent $stripe, BraintreeAgent $braintree ) {
        $this->stripe = $stripe;
        $this->braintree = $braintree;
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