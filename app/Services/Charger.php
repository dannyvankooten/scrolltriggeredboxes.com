<?php

namespace App\Services;

use App\Jobs\CreatePaymentCreditInvoice;
use App\Jobs\CreatePaymentInvoice;
use App\User;
use App\Subscription;
use App\Payment;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Stripe\Stripe;
use DateTime;
use Exception;

class Charger {

    use DispatchesJobs;

    /**
     * Charger constructor.
     */
    public function __construct() {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @param User $user
     * @param string $token
     * 
     * @return User
     */
    public function customer( User $user, $token ) {
        if( $user->stripe_customer_id ) {
            // update existing customer in Stripe
            $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
            $customer->source = $token;
            $customer->save();
        } else {

            // create a new customer in Stripe
            $customer = \Stripe\Customer::create([
                "source" => $token,
                "description" => "User #{$user->id}",
                'email' => $user->email,
                "metadata" => array(
                    "user" => $user->id
                ),
                'business_vat_id' => $user->vat_number,
            ]);

            $user->stripe_customer_id = $customer->id;
        }

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

        try {
            $refund = \Stripe\Refund::create($args);
        } catch( \Stripe\Error\InvalidRequest $e ) {

        }

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
    public function subscription( Subscription $subscription ) {
        $user = $subscription->user;
        $today = new DateTime("now");
        $intervalString = "+1 {$subscription->interval}";

        // calculate amount in cents
        $amountInCents = $subscription->getAmountInclTax() * 100;

        try {
            $charge = \Stripe\Charge::create([
                "amount" => $amountInCents,
                "currency" => "USD",
                "customer" => $user->stripe_customer_id,
                "metadata" => array(
                    "subscription_id" => $subscription->id
                )
            ]);
        } catch(\Stripe\Error\Card $e) {
           throw new Exception( $e->getMessage(), $e->getCode() );
        }

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


}