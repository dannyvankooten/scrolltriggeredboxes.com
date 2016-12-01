<?php

namespace App\Services\Payments;

use App\User;
use App\License;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementDetails;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\BillingAgreementToken;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Illuminate\Contracts\Logging\Log;

class PayPalAgent {

    /**
     * @var ApiContext
     */
    protected $paypalContext;

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
     * @param ApiContext $paypalContext
     * @param Cashier $cashier
     * @param Log $log
     */
    public function __construct( ApiContext $paypalContext, Cashier $cashier, Log $log ) {
        $this->paypalContext = $paypalContext;
        $this->log = $log;
        $this->cashier = $cashier;
    }

    /**
     * @param License $license
     * @return string
     */
    protected function getPlanId( License $license ) {
        $planIds = array(
            'personal-month' => 'P-18J79860VG454961WWYD7HEQ',
            'personal-year' => 'P-9L9144589R7374025WYD73RQ',
            'developer-month' => 'P-9NH41920R9665413KWYEAPWI',
            'developer-year' => 'P-9Y827281T6432391WWYEBLUY',
        );

        if( ! isset($planIds["{$license->plan}-{$license->interval}"])) {
            throw new \InvalidArgumentException("Unknown plan & frequency argument combination.");
        }

        $planId = $planIds["{$license->plan}-{$license->interval}"];

        return $planId;
    }

    /**
     * @param License $license
     * @throws PaymentException
     * @return string
     */
    public function createSubscription( License $license ) {
        $planId = $this->getPlanId( $license );

        $now = gmdate('Y-m-d\TH:i:s\Z', strtotime('+1 minutes'));

        $agreement = new Agreement();
        $agreement->setName('Boxzilla Premium Agreement')
            ->setDescription(sprintf('Billing agreement for Boxzilla Premium %s', ucfirst($license->plan)))
            ->setStartDate($now);

        $plan = new Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            $agreement = $agreement->create($this->paypalContext);
        } catch( \Exception $e ) {
          throw PaymentException::fromException($e);
        }

        $approvalUrl = $agreement->getApprovalLink();

        $this->log->info( sprintf( 'Created PayPal agreement %s for user %s', $license->paypal_subscription_id, $license->user->email ) );

        return $approvalUrl;
    }

    /**
     * @param License $license
     * @param $token
     * @throws PaymentException
     */
    public function startSubscription( License $license, $token ) {
        $agreement = new Agreement();

        try {
            $agreement = $agreement->execute( $token, $this->paypalContext );
        } catch( \Exception $e ) {
            throw PaymentException::fromException($e);
        }

        $license->paypal_subscription_id = $agreement->getId();
        $license->status = 'active';
        $license->deactivated_at = null;

        $this->log->info( sprintf( 'Started PayPal agreement %s for user %s', $license->paypal_subscription_id, $license->user->email ) );
    }

    /**
     * @param License $license
     * @return boolean
     * @throws PaymentException
     */
    public function isSubscriptionActive( License $license ) {
        try {
            $agreement = Agreement::get( $license->paypal_subscription_id, $this->paypalContext);
        } catch( \Exception $e ) {
            throw PaymentException::fromException($e);
        }

        return in_array($agreement->getState(), ['Active', 'Reactivated' ]);
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

        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote("Paused Boxzilla license");

        try {
            $agreement = Agreement::get( $license->paypal_subscription_id, $this->paypalContext);
            $agreement->suspend( $agreementStateDescriptor, $this->paypalContext );
        } catch( \Exception $e ) {
            throw PaymentException::fromException($e);
        }

        $license->deactivated_at = Carbon::now();
        $license->status = 'canceled';
        $license->paypal_subscription_id = null;

        $this->log->info( sprintf( 'Canceled PayPal agreement %s for user %s', $license->stripe_subscription_id, $license->user->email ) );
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

        // TODO
    }

    /**
     * @param License $license
     * @return bool
     */
    private function hasSubscription(License $license) {
        return ! empty( $license->paypal_subscription_id );
    }


}