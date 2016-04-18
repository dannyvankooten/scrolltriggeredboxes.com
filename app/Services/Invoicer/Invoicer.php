<?php

namespace App\Services\Invoicer;

use App\User;
use App\Payment;
use DateTime;

class Invoicer {

    /**
     * @var Moneybird
     */
    protected $moneybird;

    /**
     * Invoicer constructor.
     *
     * @param Moneybird $moneybird
     */
    public function __construct( Moneybird $moneybird ) {
       $this->moneybird = $moneybird;
    }

    /**
     * @param User $user
     * @param bool $updateContact
     *
     * @return User
     */
    public function contact( User $user, $updateContact = false ) {

        // bail early if user has contact already & update flag is disabled
        if( ! empty( $user->moneybird_contact_id ) && ! $updateContact ) {
            return $user;
        }

        // create contact
        $contact = [
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'company_name' => $user->company,
            'tax_number' => $user->vat_number,
            'address1' => $user->address,
            'city' => $user->city,
            'country' => $user->country,
            'zipcode' => $user->zip,
            'delivery_method' => 'Manual',
            'email' => $user->email,
        ];

        if( ! empty( $user->moneybird_contact_id ) ) {
            $this->moneybird->updateContact( $user->moneybird_contact_id, $contact );
        } else {
            $data = $this->moneybird->createContact( $contact );
            $user->moneybird_contact_id = $data->id;
        }

        return $user;
    }

    /**
     * @param Payment $payment
     * @return Payment
     *
     * TODO: Include subscription period in description
     * TODO: Resolve proper tax rate ID here (we need access to new MoneyBird.com interface for that first)
     */
    public function invoice( Payment $payment ) {

        $invoiceData = [
            'contact_id' => $payment->user->moneybird_contact_id,
            'currency' => $payment->currency,
            'invoice_data' => $payment->created_at->format('Y-m-d'),
            'prices_are_incl_tax' => false,
            'details_attributes' => [
                [
                    'description' => 'Your Boxzilla subscription',
                    'price' => $payment->subtotal,
                   // 'tax_rate_id' => '', // TODO: Fetch correct tax rate ID here.
                ]
            ]
        ];
        
        if( $payment->moneybird_invoice_id ) {
            // invoice exists, update it
            $this->moneybird->updateInvoice( $payment->moneybird_invoice_id, $invoiceData );
        } else {
            // create new invoice
            $data = $this->moneybird->createInvoice( $invoiceData );
            $payment->moneybird_invoice_id = $data->id;

            // mark invoice as sent
            $sendingData = [
                'delivery_method' => 'Manual'
            ];
            $this->moneybird->createInvoiceSending( $data->id, $sendingData );

            // register invoice payment
            // IMPORTANT: Take notice, as we're getting the total price & base price from MoneyBird here. This means we're using MoneyBirds currency exchange rate....
            $paymentData = [
                'payment_date' => $payment->created_at->format('Y-m-d H:i:s'),
                'price' => $data->total_price_incl_tax,
                'price_base' => $data->total_price_incl_tax_base
            ];

            $this->moneybird->createInvoicePayment( $data->id, $paymentData );
        }

        return $payment;
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    public function hasInvoice( Payment $payment ) {
        return ! empty( $payment->moneybird_invoice_id );
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    public function getInvoiceUrl( Payment $payment ) {
        $data = $this->moneybird->getInvoice( $payment->moneybird_invoice_id );
        return $data->url . '.pdf';
    }

    /**
     * @param Payment $payment
     */
    public function creditInvoice( Payment $payment ) {
        if( ! $payment->moneybird_invoice_id ) {
            return;
        }

        $data = $this->moneybird->createCreditInvoice( $payment->moneybird_invoice_id );
        $sendingData = [
            'delivery_method' => 'Manual'
        ];
        $this->moneybird->createInvoiceSending( $data->id, $sendingData );

        $paymentData = [
            'payment_date' => $payment->created_at->format('Y-m-d H:i:s'),
            'price' => $data->total_price_incl_tax,
            'price_base' => $data->total_price_incl_tax_base
        ];

        $this->moneybird->createInvoicePayment( $data->id, $paymentData );
    }


}