<?php

namespace App\Services\Payments;

use Exception;
use Stripe\Error\Base as StripeException;

class PaymentException extends Exception {

    /**
     * @param StripeException $e
     * @return PaymentException
     */
    public static function fromStripe( StripeException $e ) {
        return new self($e->getMessage(), $e->getCode());
    }

    /**
     * @param Exception $e
     * @return PaymentException
     */
    public static function fromException( Exception $e ) {
        return new self($e->getMessage(), $e->getCode());
    }

}