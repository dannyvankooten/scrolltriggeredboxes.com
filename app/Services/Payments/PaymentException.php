<?php

namespace App\Services\Payments;

use Exception;
use Stripe\Error\Base as StripeException;
use Braintree\Exception as BraintreeException;

class PaymentException extends Exception {

    /**
     * @param StripeException $e
     * @return PaymentException
     */
    public static function fromStripe( StripeException $e ) {
        return new self($e->getMessage(), $e->getCode());
    }

    /**
     * @param BraintreeException $e
     * @return PaymentException
     */
    public static function fromBraintree( BraintreeException $e ) {
        return new self($e->getMessage(), $e->getCode());
    }

}