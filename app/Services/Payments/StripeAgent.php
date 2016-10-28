<?php

namespace App\Services\Payments;

use App\License;
use Illuminate\Contracts\Logging\Log;
use InvalidArgumentException;
use App\Jobs\CreatePaymentCreditInvoice;
use App\Jobs\CreatePaymentInvoice;
use App\User;
use App\Payment;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Stripe;
use Stripe\Error\InvalidRequest;
use Carbon\Carbon;
use Exception;

use Stripe\Error\Base as StripeException;

class StripeAgent {

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
    public function updatePaymentMethod( User $user, $token = '' ) {

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
     * @param License $license
     * @return string
     */
    protected function getPlanId( License $license ) {
        return sprintf('boxzilla-%s-%sly', $license->getPlan(), $license->interval);
    }

    /**
     * @param License $license
     * @throws PaymentException
     */
    public function createSubscription( License $license ) {

        if( empty( $license->user->stripe_customer_id ) ) {
            throw new PaymentException( "User has no valid payment method registered." );
        }

        try {
            $stripeSubscription = Stripe\Subscription::create([
                'customer' => $license->user->stripe_customer_id,
                'plan' => $this->getPlanId($license),
            ]);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        $license->stripe_subscription_id = $stripeSubscription->id;
        $license->extend();
    }

    /**
     * @param License $license
     *
     * @throws PaymentException
     */
    public function cancelSubscription( License $license ) {
        try {
            $stripeSubscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
            $stripeSubscription->cancel();
        } catch(StripeException $e) {
            throw PaymentException::fromStripe($e);
        }
    }

    /**
     * @param License $license
     * @throws PaymentException
     */
    public function resumeSubscription( License $license ) {

        if( empty( $license->user->stripe_customer_id ) ) {
            throw new PaymentException( "User has no valid payment method registered." );
        }

        // if license is expired, create a new subscription
        if( $license->isExpired() ) {
            return $this->createSubscription($license);
        }

        try {
            // create subscription but do not charge until license expiration date
            $stripeSubscription = Stripe\Subscription::create([
                'customer' => $license->user->stripe_customer_id,
                'plan' => $this->getPlanId($license),
                'trial_end' => $license->expires_at->getTimestamp(),
                'metadata' => [
                    'license_id' => $license->id
                ],
            ]);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        $license->stripe_subscription_id = $stripeSubscription->id;
    }

    /**
     * @param License $license
     */
    public function updateNextChargeDate( License $license ) {
        $stripeSubscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
        $stripeSubscription->trial_end = $license->expires_at;
        $stripeSubscription->save();
    }

    /**
     * Refund a payment
     *
     * @param Payment $payment
     *
     * @return Payment The refund object.
     *
     * @throws PaymentException
     */
    public function refundPayment( Payment $payment )
    {
        if( $payment->isRefund() ) {
            throw new PaymentException("Payment is already a refund.");
        }

        $args = array(
            "charge" => $payment->stripe_id,
            'reason' => 'requested_by_customer'
        );

        // refund payment in stripe
        try {
            $stripeRefund = Stripe\Refund::create($args);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        // store negative opposite of payment
        $refund = new Payment();
        $refund->stripe_id = $stripeRefund->id;
        $refund->related_payment_id = $payment->id;
        $refund->user_id = $payment->user_id;
        $refund->license_id = $payment->license_id;
        $refund->subtotal = 0 - $payment->subtotal;
        $refund->tax = 0 - $payment->tax;
        $refund->save();

        // subtract one interval from license expiration date
        $license = $payment->license;
        $license->expires_at = $license->expires_at->modify("-1 {$license->interval}");
        $license->save();

        // dispatch job to create an invoice for this payment
        $this->dispatch(new CreatePaymentCreditInvoice($payment, $refund));

        // log some info
        $user = $payment->user;
        $this->logger->info( sprintf( 'Refunded a total amount of %s for user %s', $payment->getCurrencySign() . $payment->getTotal(), $user->email ) );

        return $refund;
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

            throw PaymentException::fromStripe($e);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
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