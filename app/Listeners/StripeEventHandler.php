<?php

namespace App\Listeners;

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
     * @var Logger
     */
    protected $log;

    /**
     * StripePollInvoicePaymentFailures constructor.
     *
     * @param Mailer $mailer
     * @param Log $log
     */
    public function __construct( Mailer $mailer, Log $log ) {
        $this->mailer = $mailer;
        $this->log = $log;
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
     * @return bool
     */
    protected function handleCustomerSubscriptionUpdated( Stripe\Subscription $subscription )
    {
        /** @var License $license */
        $license = License::where('stripe_subscription_id', $subscription->id)->first();
        if( ! $license ) {
            return false;
        }

        // check if local license should be active or inactive
        $active = in_array($subscription->status, ['trialing', 'active', 'past_due']);
        if( $license->isActive() !== $active ) {
            $license->status = $subscription->status;
            $license->save();
        }
    }

    /**
     * @param Stripe\Invoice $invoice
     *
     * @return bool
     */
    protected function handleInvoicePaymentFailed( Stripe\Invoice $invoice )
    {
        $subscription_id = $invoice->subscription;

        /** @var License $license */
        $license = License::with('user')->where('stripe_subscription_id', $subscription_id )->first();
        if( ! $license ) {
            return false;
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

        return true;
    }

    /**
     * @param Stripe\Invoice $invoice
     *
     * @return bool
     */
    protected function handleInvoicePaymentSucceeded( Stripe\Invoice $invoice ) {
        $subscription_id = $invoice->subscription;

        /** @var License $license */
        $license = License::where('stripe_subscription_id', $subscription_id )->first();
        if( ! $license ) {
            return false;
        }

        $existingPayment = Payment::where('stripe_id', $invoice->charge )->first();
        if( $existingPayment ) {
            return false;
        }

        $this->log->info(sprintf('Creating %s payment for Stripe charge %s', $invoice->total, $invoice->charge));

        // store local payment
        $payment = new Payment();
        $payment->created_at = Carbon::createFromTimestamp($invoice->date);
        $payment->license_id = $license->id;
        $payment->stripe_id = $invoice->charge;
        $payment->user_id = $license->user_id;
        $payment->currency = 'USD';
        $payment->subtotal = ( $invoice->subtotal / 100 );
        if( $invoice->tax ) {
            $payment->tax = ( $invoice->tax / 100 );
        }
        $payment->save();

        // extend license
        $license->extend();
        $license->save();

        return true;
    }

    /**
     * @param Stripe\Charge $charge
     * @return bool
     */
    protected function handleChargeRefunded( Stripe\Charge $charge ) {

        $payment = Payment::with('user')->where('stripe_id', $charge->id)->first();
        if( ! $payment ) {
            return false;
        }

        // do nothing if there are no refunds, just in case.
        $stripeRefunds = $charge->refunds->data;
        if( empty($stripeRefunds) ) {
            return false;
        }

        $taxRate = $payment->user->getTaxRate();

        // create local refund objects
        foreach( $stripeRefunds as $stripeRefund ) {

            // check if local refund object exists already
            $existing = Payment::where('stripe_id', $stripeRefund->id)->first();
            if($existing) {
                continue;
            }

            $this->log->info(sprintf('Creating %s refund for Stripe refund %s', $stripeRefund->total, $stripeRefund->id));

            // store negative opposite of payment
            $refund = new Payment();
            $refund->created_at = Carbon::createFromTimestamp($stripeRefund->created);
            $refund->stripe_id = $stripeRefund->id;
            $refund->related_payment_id = $payment->id;
            $refund->user_id = $payment->user_id;
            $refund->license_id = $payment->license_id;
            $refund->tax = -($taxRate * $stripeRefund->amount / 100);
            $refund->currency = $payment->currency;
            $refund->subtotal = -($stripeRefund->amount / 100) - $refund->tax;

            $refund->save();
        }

        // set new license expiration date if charge is fully refunded.
    }
}
