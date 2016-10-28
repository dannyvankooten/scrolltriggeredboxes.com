<?php

namespace App\Console\Commands;

use App\License;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

use Illuminate\Contracts\Mail\Mailer;
use Stripe;

class StripePollInvoicePaymentFailures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:poll-invoice-payment-failures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll stripe for payment failures on invoices.';

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * StripePollInvoicePaymentFailures constructor.
     *
     * @param Mailer $mailer
     */
    public function __construct( Mailer $mailer ) {
        $this->mailer = $mailer;

        parent::__construct();
    }


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
            'type' => 'invoice.payment_failed',
            'created' => [
                'gte' => strtotime('-1 hour')
            ]
        ]);

        foreach( $events->data as $event ) {
            $invoice = $event->data->object;
            $this->sendFailedPaymentEmail($invoice);
        }
    }

    /**
     * @param Stripe\Invoice $invoice
     *
     * @return bool
     */
    protected function sendFailedPaymentEmail( Stripe\Invoice $invoice )
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

}
