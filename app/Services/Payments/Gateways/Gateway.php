<?php

namespace App\Services\Payments\Gateways;

use App\License;
use App\Payment;
use App\User;

interface Gateway {

    /**
     * @param User $user
     * @param string $paymentToken
     * @return User
     */
    public function updatePaymentMethod(User $user, $paymentToken  = '');

    /**
     * @param License $license
     */
    public function createSubscription( License $license );

    /**
     * @param License $license
     */
    public function cancelSubscription( License $license );

    /**
     * @param License $license
     */
    public function updateNextChargeDate( License $license );

    /**
     * @param Payment $payment
     */
    public function refundPayment( Payment $payment );

}