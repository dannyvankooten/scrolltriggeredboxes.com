<?php

namespace App\Listeners;

use App\Services\Payments\Cashier;
use Braintree;
use Braintree\WebhookNotification;
use Illuminate\Contracts\Logging\Log;

use App\License;
use App\Payment;

use Illuminate\Contracts\Mail\Mailer;
use Carbon\Carbon;

class BraintreeEventHandler
{


    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Cashier
     */
    protected $cashier;


    /**
     * StripePollInvoicePaymentFailures constructor.
     *
     * @param Mailer $mailer
     * @param Log $log
     * @param Cashier $cashier
     */
    public function __construct( Mailer $mailer, Log $log, Cashier $cashier ) {
        $this->mailer = $mailer;
        $this->log = $log;
        $this->cashier = $cashier;
    }

    /**
     * Handle the event.
     *
     * @param  WebhookNotification  $notification
     * @return void
     */
    public function handle(WebhookNotification $notification)
    {
        $this->log->info(sprintf("Braintree event received: %s", $notification->kind));

        switch( $notification->kind ) {
            case 'subscription_charged_unsuccessfully':
                $this->handleSubscriptionChargeFailed($notification->subscription);
                break;

            case 'subscription_charged_successfully':
                $this->handleSubscriptionChargeSuccess($notification->subscription);
                break;

            case 'charge.refunded':
                // TODO
                break;

            case 'subscription_canceled':
            case 'subscription_expired':
                $this->handleSubscriptionStatusChanged($notification->subscription);
                break;
        }
    }

    protected function handleSubscriptionChargeFailed(Braintree\Subscription $braintreeSubscription) {
        /** @var License $license */
        $license = License::with('user')->where('braintree_subscription_id', $braintreeSubscription->id )->first();
        if( ! $license ) {
            $this->log->warning(sprintf('Received event for Braintree subscription %s without local license.', $braintreeSubscription->id));
            return;
        }

        $transaction = $braintreeSubscription->transactions[0];

        // create temp payment object
        $payment = new Payment();
        $payment->user = $license->user;
        $payment->currency = $transaction->currencyIsoCode;
        $payment->subtotal = $transaction->amount;
        if( $transaction->taxAmount ) {
            $payment->subtotal = $transaction->amount - $transaction->taxAmount;
            $payment->tax = ( $transaction->taxAmount / 100 );
        }

        $this->cashier->notifyAboutFailedChargeAttempt($license, $payment);
    }

    /**
     * @param Braintree\Subscription $braintreeSubscription
     */
    protected function handleSubscriptionChargeSuccess(Braintree\Subscription $braintreeSubscription) {

        /** @var License $license */
        $license = License::where('braintree_subscription_id', $braintreeSubscription->id)->first();
        if( ! $license ) {
            $this->log->warning(sprintf('Received event for Braintree subscription %s without local license.', $braintreeSubscription->id));
            return;
        }

        // record payment locally
        $this->cashier->recordBraintreePayment($license, $braintreeSubscription->transactions[0]);
    }

    /**
     * @param Braintree\Subscription $braintreeSubscription
     */
    protected function handleSubscriptionStatusChanged(Braintree\Subscription $braintreeSubscription) {
        /** @var License $license */
        $license = License::where('braintree_subscription_id', $braintreeSubscription->id)->first();
        if( ! $license ) {
            $this->log->warning(sprintf('Received event for Braintree subscription %s without local license.', $braintreeSubscription->id));
            return;
        }

        // check if local license should be active or inactive
        $active = in_array($braintreeSubscription->status, ['active', 'pending', 'past_due']);
        if( $license->isActive() !== $active ) {
            if( ! $active ) {
                $license->deactivated_at = Carbon::now();
            }

            $license->status = $braintreeSubscription->status;
            $license->save();

            $this->log->info(sprintf("Deactivated license %d for user %s", $license->id, $license->user->email));
        }
    }


}
