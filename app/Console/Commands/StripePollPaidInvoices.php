<?php

namespace App\Console\Commands;

use App\License;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

use Stripe;

class StripePollPaidInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:poll-paid-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll stripe for paid invoices.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stripeSecret = config('services.stripe.secret');
        Stripe\Stripe::setApiKey( $stripeSecret );

        // invoice.payment_succeeded
        $events = Stripe\Event::all([
            'type' => 'invoice.payment_succeeded',
            'created' => [
                'gte' => strtotime('-1 hour')
            ]
        ]);

        foreach( $events->data as $event ) {
            $invoice = $event->data->object;
            $this->createPayment($invoice);
        }
    }

    /**
     * @param Stripe\Invoice $invoice
     *
     * @return bool
     */
    protected function createPayment( Stripe\Invoice $invoice ) {
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

        $this->info(sprintf('Creating %s payment for Stripe charge %s', $invoice->total, $invoice->charge));

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

}
