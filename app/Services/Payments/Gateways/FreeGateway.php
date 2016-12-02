<?php

namespace App\Services\Payments\Gateways;

use App\Payment;
use App\Services\Payments\PaymentException;
use App\User;
use App\License;

class FreeGateway implements Gateway {


    /**
     * @param User $user
     * @param string $paymentToken
     * @return User
     *
     * @throws PaymentException
     */
    public function updatePaymentMethod( User $user, $paymentToken = '' ) {
        return $user;
    }

    /**
     * @param License $license
     */
    public function createSubscription( License $license ) {}

    /**
     * @param License $license
     * @return boolean
     * @throws PaymentException
     */
    public function isSubscriptionActive( License $license ) {
      return true;
    }

    /**
     * @param License $license
     *
     * @throws PaymentException
     */
    public function cancelSubscription( License $license ) {}

    /**
     * @param License $license
     *
     * @throws PaymentException
     */
    public function updateNextChargeDate( License $license ) {}

    /**
     * @param Payment $payment
     * @throws PaymentException
     */
    public function refundPayment( Payment $payment ) {
        throw new PaymentException('Payment exists where it should not.');
    }
}