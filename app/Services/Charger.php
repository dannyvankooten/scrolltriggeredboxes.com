<?php

namespace App\Services;

use App\Jobs\CreatePaymentCreditInvoice;
use App\Jobs\CreatePaymentInvoice;
use App\User;
use App\Subscription;
use App\Payment;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Stripe\Error\InvalidRequest;
use Stripe\Stripe;
use DateTime;
use Exception;

class Charger {

    use DispatchesJobs;

    /**
     * Charger constructor.
     *
     * @param string $stripeSecret
     */
    public function __construct( $stripeSecret ) {
        Stripe::setApiKey( $stripeSecret );
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

        // add address info if we have it
        if( count( array_filter([ $user->address, $user->city, $user->country ]) ) === 3 ) {
            $customerData['shipping'] = [
                'name' => $user->name,
                'address' => [
                    'line1' => $user->address,
                    'postal_code' => $user->zip,
                    'city' => $user->city,
                    'state' => $user->state,
                    'country' => $user->country
                ]
            ];
        }

        if( ! empty( $token ) ) {
            $customerData['source'] = $token;
        }

        if( $user->stripe_customer_id ) {
            $customer = $this->updateCustomer( $user->stripe_customer_id, $customerData );
        } else {
            $customer = $this->createCustomer( $customerData );
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
     */
    public function refund( Payment $payment )
    {
        $args = array(
            "charge" => $payment->stripe_id,
            'reason' => 'requested_by_customer'
        );


        $refund = \Stripe\Refund::create($args);

        $payment->delete();

        $subscription = $payment->subscription;
        $license = $subscription->license;

        // substract one interval from license expiration date
        $license->expires_at = $license->expires_at->modify("-1 {$subscription->interval}");
        $license->save();

        // dispatch job to create an invoice for this payment
        $this->dispatch(new CreatePaymentCreditInvoice($payment));

        return false;
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

        // calculate amount in cents
        $amountInCents = $subscription->getAmountInclTax() * 100;

        $charge = \Stripe\Charge::create([
            "amount" => $amountInCents,
            "currency" => "USD",
            "customer" => $user->stripe_customer_id,
            "metadata" => array(
                "subscription_id" => $subscription->id
            )
        ]);

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->subscription_id = $subscription->id;
        $payment->subtotal = $subscription->getAmount();
        $payment->tax = $subscription->getTaxAmount();
        $payment->stripe_id = $charge->id;
        $payment->save();

        // success! extend license
        $license = $subscription->license;

        // start counting at expiration date or from today if already expired
        $license->expires_at = $license->isExpired() ? $today->modify( $intervalString ) : $license->expires_at->modify( $intervalString );
        $license->save();

        // set new charge date
        $subscription->next_charge_at = $license->expires_at->modify('-1 week');
        $subscription->save();

        // dispatch job to create an invoice for this payment
        $this->dispatch(new CreatePaymentInvoice($payment));

        return true;
    }

    /**
     * @param Subscription $subscription
     * 
     * @return boolean
     */
    public function chargable( Subscription $subscription ) {
        return ! empty( $subscription->user->stripe_customer_id );
    }

    /**
     * @param array $data
     *
     * @return \Stripe\Customer
     */
    private function createCustomer( array $data )
    {
        if( empty( $data['source'] ) ) {
            throw new \InvalidArgumentException('A payment token must be given to create a new customer in Stripe.');
        }

        $customer = \Stripe\Customer::create($data);
        return $customer;
    }

    /**
     * @param string $id
     * @param array $data
     *
     * @return \Stripe\Customer
     *
     * @throws InvalidRequest
     */
    private function updateCustomer( $id, array $data )
    {
        try {
            $customer = \Stripe\Customer::retrieve($id);
        } catch( InvalidRequest $e ) {
            if( $e->getHttpStatus() == 404 ) {
                return $this->createCustomer( $data );
            }

            throw $e;
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