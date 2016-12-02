<?php

namespace App\Services\Payments\Gateways;

use App\Payment;
use App\Services\Payments\Cashier;
use App\Services\Payments\PaymentException;
use App\User;
use App\License;
use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;
use Braintree;

class BraintreeGateway implements Gateway {

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
        $this->cashier = $cashier;
        $this->log = $log;
    }

    /**
     * @param License $license
     * @return string
     */
    protected function getPlanId( License $license ) {
        return sprintf('boxzilla-%s-%sly', $license->getPlan(), $license->interval);
    }

    /**
     * @param string $planId
     * @return float
     */
    protected function getPlanPrice( $planId ) {
        $prices = [
            'boxzilla-personal-monthly' => 6.00,
            'boxzilla-personal-yearly' => 60.00,
            'boxzilla-developer-monthly' => 20.00,
            'boxzilla-developer-yearly' => 200.00,
        ];

        return $prices[$planId];
    }

    /**
     * @param User $user
     * @param string $paymentToken
     * @return User
     *
     * @throws PaymentException
     */
    public function updatePaymentMethod( User $user, $paymentToken = '' ) {
        $data = [
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'company' => $user->company,
            'email' => $user->email,
            'customFields' => [
                'user_id' => $user->id
            ]
         ];

        if( ! empty( $paymentToken ) ) {
            $data['paymentMethodNonce'] = $paymentToken;
        }

        // filter out empty values
        $data = array_filter( $data );

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
        $user = $license->user;

        if(! $this->hasPaymentMethod($user)) {
            throw new PaymentException('User has no valid payment method.');
        }

        // cancel current subscription first.
        if($this->hasSubscription($license)) {
            $this->cancelSubscription($license);
        }

        $planId = $this->getPlanId($license);
        $price = $this->getPlanPrice($planId);

        // add tax to price if needed
        if($user->isEligibleForTax()) {
            $price = $price * ( 1 + ( $user->getTaxRate() / 100 ) ) ;
        }

        $data = [
            'paymentMethodToken' => $user->braintree_payment_token,
            'planId' => $planId,
            'merchantAccountId' => 'boxzilla',
            'price' => $price,
        ];

        // if license expires in future, set first billing date to be in future.
        if( ! $license->isExpired() ) {
            $data['firstBillingDate'] = $license->expires_at->toDateTimeString();
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

        $this->log->info( sprintf( 'Created Braintree subscription %s for user %s', $license->braintree_subscription_id, $user->email ) );
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

        if( ! $result->success ) {
            foreach($result->errors->deepAll() AS $error) {
                // Braintree error 81905: Subscription has already been canceled.
                if( $error->code == 81905 && $error->attribute == 'status' ) {
                    continue;
                }

                throw new PaymentException( $error->message, $error->code );
            }
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

       // simply re-create subscription
       $this->createSubscription($license);
    }

    /**
     * @param Payment $payment
     * @throws PaymentException
     */
    public function refundPayment( Payment $payment ) {
        if( $payment->isRefund() ) {
            throw new PaymentException("Payment is already a refund.");
        }

        // TODO
    }

    /**
     * @param License $license
     * @return bool
     */
    private function hasSubscription(License $license) {
        return ! empty($license->braintree_subscription_id);
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