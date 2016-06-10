<?php

namespace App\Services\Payments;

use Illuminate\Contracts\Logging\Log;
use InvalidArgumentException;
use App\Jobs\CreatePaymentCreditInvoice;
use App\Jobs\CreatePaymentInvoice;
use App\User;
use App\Subscription;
use App\Payment;

use Illuminate\Foundation\Bus\DispatchesJobs;

use Stripe;
use Stripe\Error\InvalidRequest;
use DateTime;
use Exception;

use Stripe\Error\Base as StripeException;

class Charger {

    use DispatchesJobs;

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
     * @param User $user
     * @param string $token
     * 
     * @return User
     */
    public function customer( User $user, $token = '' ) {

        $customerData = [
            'email' => $user->email,
            'metadata' => [
                'country' => $user->country,
                'user_id' => $user->id
            ]
        ];

        // add vat number if we have it
        if( ! empty( $user->vat_number ) ) {
            $customerData['business_vat_id'] = $user->vat_number;
        }

//        // add address info if we have it
//        if( count( array_filter([ $user->address, $user->city, $user->country ]) ) === 3 ) {
//            $customerData['shipping'] = [
//                'name' => $user->name,
//                'address' => [
//                    'line1' => $user->address,
//                    'postal_code' => $user->zip,
//                    'city' => $user->city,
//                    'state' => $user->state,
//                    'country' => $user->country
//                ]
//            ];
//        }

        if( ! empty( $token ) ) {
            $customerData['source'] = $token;
        }

        if( $user->stripe_customer_id ) {
            $customer = $this->updateCustomer( $user->stripe_customer_id, $customerData );
            $this->logger->info( sprintf( 'Updated Stripe customer %s from user %s', $user->stripe_customer_id, $user->email ) );
        } else {

            // token is required for new customers
            if( empty(  $customerData['source'] ) ) {
                throw new InvalidArgumentException( 'Invalid card details.' );
            }

            $customer = $this->createCustomer( $customerData );
            $this->logger->info( sprintf( 'Created Stripe customer %s from user %s', $customer->id, $user->email ) );
        }

        $user->stripe_customer_id = $customer->id;
        return $user;
    }

    /**
     * Refund a payment
     *
     * @param Payment $payment
     *
     * @return boolean
     *
     * @throws PaymentException
     */
    public function refund( Payment $payment )
    {
        $args = array(
            "charge" => $payment->stripe_id,
            'reason' => 'requested_by_customer'
        );

        // refund payment in stripe
        try {
            $refund = Stripe\Refund::create($args);
        } catch( StripeException $e ) {
            throw new PaymentException( $e->getMessage(), $e->getCode() );
        }

        // delete local payment
        $payment->delete();

        // subtract one interval from license expiration date
        $subscription = $payment->subscription;
        $license = $subscription->license;
        $license->expires_at = $license->expires_at->modify("-1 {$subscription->interval}");
        $license->save();

        // dispatch job to create an invoice for this payment
        $this->dispatch(new CreatePaymentCreditInvoice($payment));

        // log some info
        $user = $payment->user;
        $this->logger->info( sprintf( 'Refunded a total amount of %s for user %s', $payment->getCurrencySign() . $payment->getTotal(), $user->email ) );

        return false;
    }

    /**
     * @param User $user
     * @param double $amount
     * @param array $metadata
     *
     * @return object
     *
     * @throws Exception
     */
    public function charge( User $user, $amount, $metadata = array() ) {

        if( empty( $user->stripe_customer_id ) ) {
            throw new PaymentException( "Invalid payment method.", 000 );
        }
        
        // add tax
        $amountInclTax = $amount;
        $taxRate = $user->getTaxRate();
        $tax = 0.00;
        if( $taxRate > 0 ) {
            $tax = $amount * ( $taxRate / 100 );
            $amountInclTax = $amount + $tax;
        }

        // calculate amount in cents
        $amountInclTaxInCents = round( $amountInclTax * 100 );

        $data = [
            "amount" => $amountInclTaxInCents,
            "currency" => "USD",
            "customer" => $user->stripe_customer_id,
        ];

        if( ! empty( $metadata ) ) {
            $data['metadata'] = $metadata;
        }

        // charge credit card in Stripe
        try {
            $charge = Stripe\Charge::create($data);
        } catch( StripeException $e ) {
            throw new PaymentException( $e->getMessage(), $e->getCode() );
        }

        // create local payment
        $payment = new Payment();
        $payment->currency = 'USD';
        $payment->user_id = $user->id;
        $payment->subtotal = $amount;
        $payment->tax = $tax;
        $payment->stripe_id = $charge->id;
        $payment->save();

        // dispatch job to create an invoice for this payment
        $this->dispatch(new CreatePaymentInvoice($payment));

        // log
        $this->logger->info( sprintf( 'Charged %s for user %s', $payment->getCurrencySign() . $payment->getTotal(), $user->email ) );

        return $payment;
    }

    /**
     * Charge a subscription
     *
     * @param Subscription $subscription
     *
     * @return bool
     * 
     * @throws Exception
     */
    public function subscription( Subscription $subscription )
    {
        $user = $subscription->user;
        $today = new DateTime("now");
        $intervalString = "+1 {$subscription->interval}";

        $payment = $this->charge( $user, $subscription->getAmount(), array(
            "subscription_id" => $subscription->id
        ));
        $payment->subscription_id = $subscription->id;
        $payment->save();

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

    /**
     * @param Subscription $subscription
     * 
     * @return boolean
     */
    public function chargeable( Subscription $subscription ) {
        return ! empty( $subscription->user->stripe_customer_id );
    }

    /**
     * @param array $data
     *
     * @return \Stripe\Customer
     *
     * @throws PaymentException
     * @throws InvalidArgumentException
     */
    private function createCustomer( array $data )
    {
        if( empty( $data['source'] ) ) {
            throw new InvalidArgumentException('A payment token must be given to create a new customer in Stripe.');
        }

        try {
            $customer = Stripe\Customer::create($data);
        } catch( StripeException $e ) {
            throw new PaymentException( $e->getMessage(), $e->getCode() );
        }

        return $customer;
    }

    /**
     * @param string $id
     * @param array $data
     *
     * @return \Stripe\Customer
     *
     * @throws PaymentException
     */
    private function updateCustomer( $id, array $data )
    {
        try {
            $customer = Stripe\Customer::retrieve($id);
        } catch( InvalidRequest $e ) {
            //  customer does not exist in stripe, so create it.
            if( $e->getHttpStatus() == 404 ) {
                return $this->createCustomer( $data );
            }

            throw new PaymentException( $e->getMessage(), $e->getCode() );
        } catch( StripeException $e ) {
            throw new PaymentException( $e->getMessage(), $e->getCode() );
        }

        foreach( $data as $property => $value ) {
            if( ! empty( $value ) && $customer->$property != $value ) {
                $customer->$property = $value;
            }
        }

        $customer->save();
        return $customer;
    }


}