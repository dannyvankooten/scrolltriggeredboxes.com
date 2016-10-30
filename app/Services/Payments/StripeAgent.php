<?php

namespace App\Services\Payments;

use App\License;
use App\User;
use App\Payment;

use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;

use Exception;
use InvalidArgumentException;

use Stripe;
use Stripe\Error\InvalidRequest;
use Stripe\Error\Base as StripeException;

class StripeAgent {

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Cashier
     */
    protected $cashier;

    /**
     * Charger constructor.
     *
     * @param string $stripeSecret
     * @param Cashier $cashier
     * @param Log $log
     */
    public function __construct( $stripeSecret, Cashier $cashier, Log $log ) {
        Stripe\Stripe::setApiKey( $stripeSecret );
        $this->log = $log;
        $this->cashier = $cashier;
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

        if( $this->hasCustomer($user) ) {
            $stripeCustomer = $this->updateOrCreateInStripe( Stripe\Customer::class, $user->stripe_customer_id, $customerData );
            $this->log->info( sprintf( 'Updated Stripe customer %s from user %s', $user->stripe_customer_id, $user->email ) );
        } else {
            // token is required for new customers
            if( empty( $customerData['source'] ) ) {
                throw new InvalidArgumentException( 'Invalid card details.' );
            }

            $stripeCustomer = $this->createInStripe( Stripe\Customer::class, $customerData );
            $this->log->info( sprintf( 'Created Stripe customer %s from user %s', $stripeCustomer->id, $user->email ) );
        }

        $user->stripe_customer_id = $stripeCustomer->id;
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
     * @return boolean
     */
    public function isSubscriptionActive( License $license ) {
        try {
            $stripeSubscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
        } catch(StripeException $e) {
            return false;
        }

        return in_array($stripeSubscription->status, ['trialing', 'active', 'past_due']);
    }

    /**
     * @param License $license
     * @throws PaymentException
     */
    public function createSubscription( License $license ) {

        if( ! $this->hasCustomer($license->user) ) {
            throw new PaymentException( "User has no valid payment method." );
        }

        // cancel current subscription first.
        if( $this->hasSubscription($license) ){
            $this->cancelSubscription($license);
        }

        // create new subscription
        $data = [
            'customer' => $license->user->stripe_customer_id,
            'plan' => $this->getPlanId($license),
            'tax_percent' => $license->user->getTaxRate(),
            'metadata' => [
                'license_id' => $license->id
            ],
        ];

        // if license is still valid, make sure new period does not kick in until license expiration
        if( ! $license->isExpired() ) {
            $data['prorate'] = false;
            $data['trial_end'] = $license->expires_at->getTimestamp();
        }

        try {
            $stripeSubscription = Stripe\Subscription::create($data);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        $license->stripe_subscription_id = $stripeSubscription->id;
        $license->status = 'active';

        $this->log->info( sprintf( 'Created Stripe subscription %s for user %s', $license->stripe_subscription_id, $license->user->email ) );
    }

    /**
     * @param License $license
     *
     * @throws PaymentException
     */
    public function cancelSubscription( License $license ) {

        // do nothing if license has no stripe subscription
        if( ! $this->hasSubscription($license) ) {
            return;
        }

        try {
            $stripeSubscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
            $stripeSubscription->cancel();
        } catch( InvalidRequest $e ) {
            if( $e->getHttpStatus() != 404 ) {
                throw PaymentException::fromStripe($e);
            } else {
                // ignore 404 errors as that means subscription does not even exist.
            }
        } catch(StripeException $e) {
            throw PaymentException::fromStripe($e);
        }

        $license->status = 'canceled';
        $license->stripe_subscription_id = null;
        $this->log->info( sprintf( 'Canceled Stripe subscription %s for user %s', $license->stripe_subscription_id, $license->user->email ) );
    }

    /**
     * @param License $license
     *
     * @throws PaymentException
     */
    public function updateNextChargeDate( License $license ) {

        // do nothing if license has no subscription
        if( ! $this->hasSubscription($license) ) {
            return;
        }

        try {
            $stripeSubscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
            $stripeSubscription->prorate = false;
            $stripeSubscription->trial_end = $license->expires_at->getTimestamp();
            $stripeSubscription->save();
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        $this->log->info( sprintf( 'Updated Stripe subscription %s next charge date for user %s', $license->stripe_subscription_id, $license->user->email ) );
    }

    /**
     * Refund a payment
     *
     * @param Payment $payment
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

        // subtract one interval from license expiration date
        $license = $payment->license;
        $license->expires_at = $license->expires_at->modify("-1 {$license->interval}");
        $license->save();

        // record refund right away
        $this->cashier->recordRefund($payment, $stripeRefund);

        // log some info
        $user = $payment->user;
        $this->log->info( sprintf( 'Refunded a total amount of %s for user %s', $payment->getCurrencySign() . $payment->getTotal(), $user->email ) );
    }

    /**
     * @param string $class
     * @param array $data
     * @return object
     *
     * @throws PaymentException
     */
    private function createInStripe( $class, $data ) {
        try {
            $stripeObject = $class::create($data);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        return $stripeObject;
    }

    /**
     * @param string $class
     * @param string $id
     * @param array $data
     * @return object
     * @throws PaymentException
     */
    private function updateOrCreateInStripe( $class, $id, array $data ) {
        try {
            $stripeObject = $class::retrieve($id);
        } catch( InvalidRequest $e ) {
            //  customer does not exist in stripe, so create it.
            if( $e->getHttpStatus() == 404 ) {
                return $this->createInStripe( $class, $data );
            }

            throw PaymentException::fromStripe($e);
        } catch( StripeException $e ) {
            throw PaymentException::fromStripe($e);
        }

        $stripeObject = $this->modifyObject($stripeObject, $data);
        $stripeObject->save();

        return $stripeObject;
    }

    /**
     * @param object $object
     * @param array $data
     *
     * @return object
     */
    private function modifyObject( $object, $data ) {
        foreach( $data as $property => $value ) {
            if( $object->$property != $value ) {
                $object->$property = $value;
            }
        }

        return $object;
    }

    /**
     * @param User $user
     * @return bool
     */
    private function hasCustomer(User $user) {
        return ! empty($user->stripe_customer_id);
    }

    /**
     * @param License $license
     * @return bool
     */
    private function hasSubscription(License $license) {
        return ! empty($license->stripe_subscription_id);
    }

}