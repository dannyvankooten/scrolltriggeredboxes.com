<?php

namespace App\Console\Commands;

use App\License;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

use Stripe;

class StripePollRefundedCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:poll-refunded-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll stripe for refunded charges';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stripeSecret = config('services.stripe.secret');
        Stripe\Stripe::setApiKey( $stripeSecret );

        // charge.refunded
        $events = Stripe\Event::all([
            'type' => 'charge.refunded',
            'created' => [
                'gte' => strtotime('-1 hour')
            ]
        ]);

        foreach( $events->data as $event ) {
            $this->createRefundPayment( $event->data->object );
        }
    }

    /**
     * @param Stripe\Charge $charge
     * @return bool
     */
    protected function createRefundPayment( Stripe\Charge $charge ) {

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

            $this->info(sprintf('Creating %s refund for Stripe refund %s', $stripeRefund->total, $stripeRefund->id));

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
