<?php

namespace App\Listeners;

use App\Services\Payments\Cashier;
use Illuminate\Contracts\Logging\Log;
use App\Services\Payments\PayPalEvent;

use App\License;
use App\Payment;

use Illuminate\Contracts\Mail\Mailer;
use Carbon\Carbon;

class PayPalEventHandler
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
     * @param  PayPalEvent  $event
     * @return void
     */
    public function handle(PayPalEvent $event)
    {
        $this->log->info(sprintf("PayPal event received: %s", $event->event_type));

        switch( $event->type ) {
            case 'invoice.payment_failed':

                break;

            case 'invoice.payment_succeeded':

                break;

            case 'charge.refunded':

                break;

            case 'customer.subscription.updated':

                break;
        }
    }


}
