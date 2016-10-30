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
    protected $log;

    /**
     * Charger constructor.
     *
     * @param string $stripeSecret
     * @param Log $log
     */
    public function __construct( $stripeSecret, Log $log ) {
        Stripe\Stripe::setApiKey( $stripeSecret );
        $this->log = $log;
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

        if( ! empty($user->stripe_customer_id) ) {
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

        if( empty($license->user->stripe_customer_id) ) {
            throw new PaymentException( "User has no valid payment method." );
        }

        // cancel current subscription first.
        if(!empty($license->stripe_subscription_id)){
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
        if( empty( $license->stripe_subscription_id ) ) {
            return;
        }

        try {
            $stripeSubscription = Stripe\Subscription::retrieve($license->stripe_subscription_id);
            $stripeSubscription->cancel();
        } catch( InvalidRequest $e ) {
            if( $e->getHttpStatus() != 404 ) {
                throw PaymentException::fromStripe($e);
            } else {
                // ignore 404 errors
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
        $this->log->info( sprintf( 'Refunded a total amount of %s for user %s', $payment->getCurrencySign() . $payment->getTotal(), $user->email ) );

        return $refund;
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

}