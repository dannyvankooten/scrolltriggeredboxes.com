<?php

namespace App\Services\Payments;

use App\Payment;
use App\User;
use App\License;
use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;
use Braintree;

class BraintreeAgent {

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Cashier
     */
    protected $cashier;

    /**
     * Broker constructor.
     *
     * @param Cashier $cashier
     * @param Log $log
     */
    public function __construct( Cashier $cashier, Log $log ) {
        $this->log = $log;
        $this->cashier = $cashier;
    }

    /**
     * @param License $license
     * @return string
     */
    protected function getPlanId( License $license ) {
        return sprintf('boxzilla-%s-%sly', $license->getPlan(), $license->interval);
    }

    /**
     * @param User $user
     * @param string $token
     * @return User
     *
     * @throws PaymentException
     */
    public function updatePaymentMethod( User $user, $token = '' ) {

        $data = [
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'company' => $user->company,
            'email' => $user->email,
            'customFields' => [
                'user_id' => $user->id
            ]
         ];

        if( ! empty( $token ) ) {
            $data['paymentMethodNonce'] = $token;
        }
        if( ! $user->braintree_customer_id ) {
            // create customer in braintree
            $result = Braintree\Customer::create($data);
        } else {
            $result = Braintree\Customer::update( $user->braintree_customer_id, $data );
        }

        if( ! $result->success ) {
            foreach($result->errors->deepAll() AS $error) {
                throw new PaymentException( $error->message, $error->code );
            }

            throw new PaymentException('Unspecified Briantree error.');
        }

        $user->braintree_customer_id = $result->customer->id;
        $user->braintree_payment_token = $result->customer->paymentMethods[0]->token;

        return $user;
    }

    /**
     * @param License $license
     * @throws PaymentException
     */
    public function createSubscription( License $license ) {
        if(! $this->hasPaymentMethod($license->user)) {
            throw new PaymentException('User has no valid payment method.');
        }

        // cancel current subscription first.
        if($this->hasSubscription($license)) {
            $this->cancelSubscription($license);
        }

        // TODO: Add user tax to subscription

        $data = [
            'paymentMethodToken' => $license->user->braintree_payment_token,
            'planId' => $this->getPlanId($license),
            'merchantAccountId' => 'boxzilla',
        ];

        if( ! $license->isExpired() ) {
            $data['nextBillingDate'] = $license->expires_at->format('Y-m-d H:i:s');
        }

        $result = Braintree\Subscription::create($data);

        if( ! $result->success ) {
            foreach($result->errors->deepAll() AS $error) {
                throw new PaymentException( $error->message, $error->code );
            }

            throw new PaymentException('Unspecified Briantree error.');
        }

        $license->braintree_subscription_id = $result->subscription->id;
        $license->status = 'active';
        $license->deactivated_at = null;

        $this->log->info( sprintf( 'Created Braintree subscription %s for user %s', $license->paypal_subscription_id, $license->user->email ) );
    }

    /**
     * @param License $license
     * @return boolean
     * @throws PaymentException
     */
    public function isSubscriptionActive( License $license ) {
      try {
          $subscription = Braintree\Subscription::find($license->braintree_subscription_id);
      } catch( Braintree\Exception $e ) {
          return false;
      }

      return in_array($subscription->status, [ 'active', 'pending', 'past_due']);
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
            $result = Braintree\Subscription::cancel($license->braintree_subscription_id);
        } catch( Braintree\Exception\NotFound $e ) {
            // 404 is good, means subscription does not exist.
            $result = (object) [ 'success' => true ];
        } catch( Braintree\Exception $e ) {
            throw PaymentException::fromBraintree($e);
        }

        // TODO: DRY this.
        if( ! $result->success ) {
            foreach($result->errors->deepAll() AS $error) {
                throw new PaymentException( $error->message, $error->code );
            }

            throw new PaymentException('Unspecified Briantree error.');
        }


        $this->log->info( sprintf( 'Canceled Braintree subscription %s for user %s', $license->braintree_subscription_id, $license->user->email ) );

        $license->deactivated_at = Carbon::now();
        $license->status = 'canceled';
        $license->braintree_subscription_id = null;
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

        $result = Braintree\Subscription::update( $license->braintree_subscription_id, [
            'nextBillingDate' => $license->expires_at->format('Y-m-d H:i:s')
        ]);

        $this->log->info( sprintf( 'Updated Braintree subscription %s next charge date for user %s', $license->braintree_subscription_id, $license->user->email ) );

    }

    /**
     * @param License $license
     * @return bool
     */
    private function hasSubscription(License $license) {
        return ! empty( $license->paypal_subscription_id );
    }

    /**
     * @param User $user
     * @return bool
     */
    private function hasPaymentMethod(User $user) {
        return ! empty($user->braintree_payment_token);
    }

    /**
     * @return array
     */
    public function generateClientToken() {
        return Braintree\ClientToken::generate();
    }

}