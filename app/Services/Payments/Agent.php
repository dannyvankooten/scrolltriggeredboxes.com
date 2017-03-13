<?php

namespace App\Services\Payments;

use App\License;
use App\Payment;
use App\Services\Payments\Gateways\BraintreeGateway;
use App\Services\Payments\Gateways\FreeGateway;
use App\Services\Payments\Gateways\StripeGateway;
use App\User;

class Agent {

    /**
     * @var StripeGateway
     */
    private $stripe;

    /**
     * @var BraintreeGateway
     */
    private $braintree;

    /**
     * @var FreeGateway
     */
    private $free;

    /**
     * Agent constructor.
     *
     * @param StripeGateway $stripe
     * @param BraintreeGateway $braintree
     */
    public function __construct( StripeGateway $stripe, BraintreeGateway $braintree ) {
        $this->stripe = $stripe;
        $this->braintree = $braintree;
        $this->free = new FreeGateway();
    }

    /**
     * @param User|License $object
     *
     * @return StripeGateway|BraintreeGateway|FreeGateway
     */
    public function getGateway($object) {
        if( ! empty( $object->payment_method ) ) {
            return $this->{$object->payment_method};
        }

        return $this->free;
    }

    /**
     * @param User $user
     * @param string $paymentToken
     * @return User
     */
    public function updatePaymentMethod(User $user, $paymentToken  = '') {
        return $this->getGateway($user)->updatePaymentMethod($user, $paymentToken );
    }

    /**
     * @param License $license
     * @return string
     */
    public function createSubscription( License $license ) {
        return $this->getGateway($license->user)->createSubscription($license);
    }

    /**
     * @param License $license
     */
    public function cancelSubscription( License $license ) {
        return $this->getGateway($license)->cancelSubscription($license);
    }

    /**
     * @param License $license
     */
    public function updateNextChargeDate( License $license ) {
        return $this->getGateway($license)->updateNextChargeDate($license);
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