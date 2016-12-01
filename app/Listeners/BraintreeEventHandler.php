<?php

namespace App\Listeners;

use App\Services\Payments\Cashier;
use Braintree\WebhookNotification;
use Illuminate\Contracts\Logging\Log;

use App\License;
use App\Payment;

use Illuminate\Contracts\Mail\Mailer;
use Carbon\Carbon;

class BraintreeEventHandler
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
     * @param  WebhookNotification  $notification
     * @return void
     */
    public function handle(WebhookNotification $notification)
    {
        $this->log->info(sprintf("Braintree event received: %s", $notification->kind ));

        switch( $notification->type ) {
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
