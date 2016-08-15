<?php

namespace App\Jobs;

use App\Payment;
use App\Services\Invoicer\Invoicer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreatePaymentCreditInvoice extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var Payment
     */
    protected $refund;

    /**
     * Create a new job instance.
     *
     * @param Payment $payment
     * @param Payment $refund
     */
    public function __construct( Payment $payment, Payment $refund )
    {
        $this->payment = $payment;
        $this->refund = $refund;
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
        $refund = $invoicer->creditInvoice( $this->payment, $this->refund );
        $refund->save();
    }
}
