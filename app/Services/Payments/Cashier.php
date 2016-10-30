<?php

namespace App\Services\Payments;

use App\Jobs\CreatePaymentInvoice;
use App\License;
use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Stripe;
use App\Payment;

class Cashier {

    use DispatchesJobs;

    /**
     * @var Log
     */
    protected $log;

    /**
     * Cashier constructor.
     * @param Log $log
     */
    public function __construct( Log $log ) {
        $this->log = $log;
    }

    /**
     * @param License $license
     * @param Stripe\Invoice $stripeInvoice
     */
    public function recordPayment( License $license, Stripe\Invoice $stripeInvoice) {
        $existingPayment = Payment::where('stripe_id', $stripeInvoice->charge )->first();
        if( $existingPayment ) {
            return;
        }

        // store local payment
        $payment = new Payment();
        $payment->created_at = Carbon::createFromTimestamp($stripeInvoice->date);
        $payment->license_id = $license->id;
        $payment->stripe_id = $stripeInvoice->charge;
        $payment->user_id = $license->user_id;
        $payment->currency = 'USD';
        $payment->subtotal = ( $stripeInvoice->subtotal / 100 );
        if( $stripeInvoice->tax ) {
            $payment->tax = ( $stripeInvoice->tax / 100 );
        }
        $payment->save();

        // log
        $this->log->info(sprintf('Recorded %s payment for Stripe invoice %s', $payment->getFormattedTotal(), $stripeInvoice->id));

        // dispatch job to create invoice
        $this->dispatch(new CreatePaymentInvoice($payment));
    }

    /**
     * @param Payment $payment
     *
     * @param Stripe\Refund $stripeRefund
     */
    public function recordRefund( Payment $payment, Stripe\Refund $stripeRefund ) {
        // check if local refund object exists already
        $existing = Payment::where('stripe_id', $stripeRefund->id)->first();
        if($existing) {
            return;
        }

        // get user tax rate
        $taxRate = $payment->user->getTaxRate();

        // calculate subtotal & tax amount
        $amount = $stripeRefund->amount / 100; // stripe amount is in cents
        $subtotal = $amount;
        $tax = 0.00;

        if( $taxRate > 0 ) {
            $subtotal = $amount / ( 1 + $taxRate / 100 );
            $tax = $amount - $subtotal;
        }


        // store negative opposite of payment
        $refund = new Payment();
        $refund->created_at = Carbon::createFromTimestamp($stripeRefund->created);
        $refund->stripe_id = $stripeRefund->id;
        $refund->related_payment_id = $payment->id;
        $refund->user_id = $payment->user_id;
        $refund->license_id = $payment->license_id;
        $refund->tax = 0 - $tax;
        $refund->currency = $payment->currency;
        $refund->subtotal = 0 - $subtotal;
        $refund->save();

        // log
        $this->log->info(sprintf('Recorded %s refund for Stripe charge refund %s', $refund->getFormattedTotal(), $stripeRefund->id));

        // dispatch job to create credit invoice
        $this->dispatch(new CreatePaymentInvoice($refund));
    }

}