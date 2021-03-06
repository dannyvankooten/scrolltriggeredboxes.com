<?php

namespace App\Console\Commands;

use App\Services\Invoicer\Invoicer;
use Illuminate\Console\Command;
use App\Payment;

class InvoicesCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates invoices for payments that do not yet have one.';

    /**
     * @var Invoicer
     */
    protected $invoicer;

    /**
     * Create a new command instance.
     *
     * @param Invoicer $invoicer
     */
    public function __construct( Invoicer $invoicer )
    {
        parent::__construct();

        $this->invoicer = $invoicer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(  )
    {
        // find all payments without an invoice

        /** @var Payment[] $payments */
        $payments = Payment::where('moneybird_invoice_id', null)
            ->with('user')
            ->get();

        $this->info( sprintf( '%d payments without an invoice found.', count( $payments ) ) );

        foreach( $payments as $payment ) {
            $user = $payment->user;

            // log some info
            $this->info( sprintf( 'Invoicing %s for %s', $payment->getFormattedTotal(), $user->email ) );

            // create or update contact
            $this->invoicer->updateContact( $user );

            // create or update invoice
            $this->invoicer->updateInvoice( $payment );

            $user->save();
            $payment->save();
        }

        $this->info( sprintf( 'All done. %d payments were invoiced.', count( $payments ) ) );
    }
}
