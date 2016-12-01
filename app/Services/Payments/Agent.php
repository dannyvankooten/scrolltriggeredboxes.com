<?php

namespace App\Services\Payments;

use App\License;
use App\Payment;
use App\User;

class Agent {

    /**
     * @var StripeAgent
     */
    private $stripe;

    /**
     * @var BraintreeAgent
     */
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
     * @param User $user
     * @param string $paymentToken
     * @return User
     */
    public function updatePaymentMethod(User $user, $paymentToken  = '') {
        return $this->{$user->payment_method}->updatePaymentMethod($user, $paymentToken );
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

    /**
     * @param Payment $payment
     */
    public function refundPayment( Payment $payment ) {
        if(! empty($payment->stripe_id)) {
            return $this->stripe->refundPayment($payment);
        }

        if(! empty($payment->braintree_id)) {
            return $this->braintree->refundPayment($payment);
        }
    }

}