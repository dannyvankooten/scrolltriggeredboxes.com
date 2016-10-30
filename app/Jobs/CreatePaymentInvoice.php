<?php

namespace App\Jobs;

use App\Payment;
use App\Services\Invoicer\Invoicer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreatePaymentInvoice extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * Create a new job instance.
     *
     * @param Payment $payment
     */
    public function __construct( Payment $payment )
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @param Invoicer $invoicer
     *
     * @return void
     */
    public function handle( Invoicer $invoicer )
    {
        $payment = $this->payment;
        $user = $this->payment->user;

        // create or update contact
        $invoicer->updateContact( $user );

        // create or update invoice
        $invoicer->updateInvoice( $payment );

        $user->save();
        $payment->save();
    }
}
