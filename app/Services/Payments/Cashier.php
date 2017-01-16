<?php

namespace App\Services\Payments;

use App\Jobs\CreatePaymentInvoice;
use App\License;
use Carbon\Carbon;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Stripe;
use App\Payment;
use Braintree;

class Cashier {

    use DispatchesJobs;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var Log
     */
    protected $log;

    /**
     * Cashier constructor.
     * @param Mailer $mailer
     * @param Log $log
     */
    public function __construct( Mailer $mailer, Log $log ) {
        $this->mailer = $mailer;
        $this->log = $log;
    }

    /**
     * @param License $license
     * @param Payment $payment
     */
    private function recordPayment(License $license, Payment $payment ) {
        if( $this->exists($payment) ) {
            return;
        }

        $user = $license->user;
        $payment->license_id = $license->id;
        $payment->user_id = $user->id;

        // calculate tax
        if( $user->isEligibleForTax() && ! $payment->tax ) {
            $newSubtotal = $payment->subtotal / ( 1 + ( $user->getTaxRate() / 100 ) );
            $payment->tax = $payment->subtotal - $newSubtotal;
            $payment->subtotal = $newSubtotal;
        }

        $payment->save();

        // log
        $gateway = empty( $payment->stripe_id ) ? 'Braintree' : 'Stripe';
        $remoteId = empty( $payment->stripe_id ) ? $payment->braintree_id : $payment->stripe_id;
        $this->log->info(sprintf('Recorded %s payment for %s transaction %s', $payment->getFormattedTotal(), $gateway, $remoteId));

        // extend license
        $license->extend();
        $license->save();

        // dispatch job to create invoice
        $this->dispatch(new CreatePaymentInvoice($payment));

        // log some info
        $this->log->info(sprintf('Received payment for license %d, extended with 1 %s', $license->id, $license->interval));
    }

    /**
     * @param License $license
     * @param Braintree\Transaction $transaction
     */
    public function recordBraintreePayment( License $license, Braintree\Transaction $transaction ) {
        $payment = new Payment();
        $payment->created_at = Carbon::instance($transaction->createdAt);
        $payment->braintree_id = $transaction->id;
        $payment->currency = $transaction->currencyIsoCode;
        $payment->subtotal = $transaction->amount;

        return $this->recordPayment( $license, $payment );
    }

    /**
     * @param License $license
     * @param Stripe\Invoice $stripeInvoice
     */
    public function recordStripePayment( License $license, Stripe\Invoice $stripeInvoice) {
        // store local payment
        $payment = new Payment();
        $payment->created_at = Carbon::createFromTimestamp($stripeInvoice->date);
        $payment->stripe_id = $stripeInvoice->charge;
        $payment->currency = 'USD';
        $payment->subtotal = ( $stripeInvoice->subtotal / 100 );
        if( $stripeInvoice->tax ) {
            $payment->tax = ( $stripeInvoice->tax / 100 );
        }

        return $this->recordPayment($license, $payment);
    }

    /**
     * @param Payment $payment
     * @param Payment $refund
     */
    public function recordRefund( Payment $payment, Payment $refund )
    {
        if( $this->exists($refund) ) {
            return;
        }

        $user = $payment->user;

        $refund->related_payment_id = $payment->id;
        $refund->license_id = $payment->license_id;
        $refund->user_id = $payment->user_id;
        $refund->related_payment_id = $payment->id;
        $refund->currency = $payment->currency;

        if( $user->isEligibleForTax() ) {
            $taxRate = $user->getTaxRate();
            $subtotal = $refund->subtotal / ( 1 + $taxRate / 100 );
            $tax = $refund->subtotal - $subtotal;
            $refund->subtotal = $subtotal;
            $refund->tax = $tax;
        }

        $refund->save();

        // log
        $method = empty( $refund->stripe_id ) ? 'Braintree' : 'Stripe';
        $remoteId = empty( $refund->stripe_id ) ? $refund->braintree_id : $refund->stripe_id;
        $this->log->info(sprintf('Recorded %s refund for %s charge %s', $refund->getFormattedTotal(), $method, $remoteId));

        // dispatch job to create credit invoice
        $this->dispatch(new CreatePaymentInvoice($refund));
    }

    /**
     * @param Payment $payment
     *
     * @param Stripe\Refund $stripeRefund
     */
    public function recordStripeRefund( Payment $payment, Stripe\Refund $stripeRefund )
    {
        // calculate subtotal
        $amount = $stripeRefund->amount / 100; // stripe amount is in cents

        // store negative opposite of payment
        $refund = new Payment();
        $refund->created_at = Carbon::createFromTimestamp($stripeRefund->created);
        $refund->stripe_id = $stripeRefund->id;
        $refund->subtotal = 0 - $amount;

        $this->recordRefund($payment, $refund);
    }

    /**
     * @param Payment $payment
     *
     * @param Braintree\Transaction $braintreeTransaction
     */
    public function recordBraintreeRefund( Payment $payment, Braintree\Transaction $braintreeTransaction )
    {
        // calculate subtotal
        $amount = $braintreeTransaction->amount;

        // store negative opposite of payment
        $refund = new Payment();
        $refund->created_at = Carbon::instance($braintreeTransaction->createdAt);
        $refund->braintree_id = $braintreeTransaction->id;
        $refund->subtotal = 0 - $amount;

        $this->recordRefund($payment, $refund);
    }

    /**
     * @param License $license
     * @param Payment $payment
     */
    public function notifyAboutFailedChargeAttempt(License $license, Payment $payment)
    {
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
     * @param Payment $payment
     *
     * @return bool
     */
    private function exists( Payment $payment ) {
        if( $payment->braintree_id ) {
            return !!Payment::where('braintree_id', $payment->braintree_id)->first();
        }

        if( $payment->stripe_id ) {
            return !!Payment::where('stripe_id', $payment->stripe_id)->first();
        }

        return false;
    }
}