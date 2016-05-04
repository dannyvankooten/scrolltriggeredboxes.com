<?php

namespace App\Jobs;

use App\Payment;
use App\Services\Invoicer\Invoicer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreatePaymentCreditInvoice extends Job implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var int
     */
    protected $invoiceId;

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
        $invoicer->creditInvoice( $this->payment );
    }
}
