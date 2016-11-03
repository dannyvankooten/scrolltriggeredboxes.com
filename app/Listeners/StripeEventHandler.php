<?php

namespace App\Listeners;

use App\Services\Payments\Cashier;
use Illuminate\Contracts\Logging\Log;
use Stripe;
use App\License;
use App\Payment;
use Illuminate\Contracts\Mail\Mailer;
use Carbon\Carbon;

class StripeEventHandler
{
    /**
     * @var Mailer
     */
    protected $mailer;

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
     * @param  Stripe\Event  $event
     * @return void
     */
    public function handle(Stripe\Event $event)
    {
        $this->log->info(sprintf("Stripe event received: %s", $event->type));

        switch( $event->type ) {
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleCustomerSubscriptionUpdated($event->data->object);
                break;
        }
    }

    /**
     * @param Stripe\Subscription $subscription
     */
    protected function handleCustomerSubscriptionUpdated( Stripe\Subscription $subscription )
    {
        /** @var License $license */
        $license = License::where('stripe_subscription_id', $subscription->id)->first();
        if( ! $license ) {
            return;
        }

        // check if local license should be active or inactive
        $active = in_array($subscription->status, ['trialing', 'active', 'past_due']);
        if( $license->isActive() !== $active ) {
            if( ! $active ) {
                $license->deactivated_at = Carbon::now();
            }

            $license->status = $subscription->status;
            $license->save();
        }
    }

    /**
     * @param Stripe\Invoice $invoice
     */
    protected function handleInvoicePaymentFailed( Stripe\Invoice $invoice )
    {
        $subscription_id = $invoice->subscription;

        // sanity check
        if(empty($invoice->charge)) {
            return;
        }

        /** @var License $license */
        $license = License::with('user')->where('stripe_subscription_id', $subscription_id )->first();
        if( ! $license ) {
            return;
        }

        // create temp payment object
        $payment = new Payment();
        $payment->user = $license->user;
        $payment->currency = $invoice->currency;
        $payment->subtotal = $invoice->subtotal / 100;
        if( $invoice->tax ) {
            $payment->tax = $invoice->tax / 100;
        }

        $this->mailer->send( 'emails.failed-payment', [ 'payment' => $payment ], function( $email ) use( $payment ) {
            $user = $payment->user;

            /**
             * @var \Illuminate\Mail\Message $email
             */
            $email
                ->to( $user->email, $user->name )
                ->subject( 'Boxzilla Plugin - Payment Failure' );
        });

        $this->log->info(sprintf('Emailed %s about failed %s charge for license %d', $payment->user->email, $payment->getFormattedTotal(), $license->id));
    }

    /**
     * @param Stripe\Invoice $invoice
     */
    protected function handleInvoicePaymentSucceeded( Stripe\Invoice $invoice ) {

        $subscription_id = $invoice->subscription;

        // skip "0" invoices without a charge.
        if( empty($invoice->charge) ) {
            return;
        }

        /** @var License $license */
        $license = License::where('stripe_subscription_id', $subscription_id)->first();
        if( ! $license ) {
            return;
        }

        // extend license
        $license->extend();
        $license->save();

        // log some info
        $this->log->info(sprintf('Received payment for license %d, extended with 1 %s', $license->id, $license->interval));

        // record payment locally
        $this->cashier->recordPayment($license, $invoice);
    }

    /**
     * @param Stripe\Charge $charge
     */
    protected function handleChargeRefunded( Stripe\Charge $charge ) {

        /** @var Payment $payment */
        // don't bother if we don't even have a local object for the charge in question
        $payment = Payment::with('user')->where('stripe_id', $charge->id)->first();
        if( ! $payment ) {
            return;
        }

        $stripeRefunds = $charge->refunds->data;
        foreach( $stripeRefunds as $stripeRefund ) {

            // record refunds locally
            $this->cashier->recordRefund($payment, $stripeRefund);
        }

    }
}
