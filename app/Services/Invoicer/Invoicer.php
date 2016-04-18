<?php

namespace App\Services\Invoicer;

use App\User;
use App\Payment;
use DateTime;
use GuzzleHttp\Client;

class Invoicer {

    /**
     * @var string
     */
    protected $url;

    /**
     * Invoicer constructor.
     *
     * @param string $administrationId
     * @param string $token
     */
    public function __construct( $administrationId, $token ) {

        $this->url = 'https://moneybird.com/api/v2/' . $administrationId . '/';
        $this->token = $token;

        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
             ]
        ]);
    }

    /**
     * @param User $user
     * @return User
     */
    public function contact( User $user ) {

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

        if( $user->moneybird_contact_id ) {
            $this->updateContact( $user->moneybird_contact_id, $contact );
        } else {
            $data = $this->createContact( $contact );
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
            $this->updateInvoice( $payment->moneybird_invoice_id, $invoiceData );
        } else {
            // create new invoice
            $data = $this->createInvoice( $invoiceData );
            $payment->moneybird_invoice_id = $data->id;

            // mark invoice as sent
            $sendingData = [
                'delivery_method' => 'Manual'
            ];
            $this->createInvoiceSending( $data->id, $sendingData );

            // register invoice payment
            // IMPORTANT: Take notice, as we're getting the total price & base price from MoneyBird here. This means we're using MoneyBirds currency exchange rate....
            $paymentData = [
                'payment_date' => $payment->created_at->format('Y-m-d H:i:s'),
                'price' => $data->total_price_incl_tax,
                'price_base' => $data->total_price_incl_tax_base
            ];

            $this->createInvoicePayment( $data->id, $paymentData );
        }

        return $payment;
    }


    /**
     * @param array $data
     * @return object
     */
    public function createContact( array $data ) {
        return $this->request( 'POST', 'contacts', [ 'contact' => $data ]);
    }

    /**
     * @param int $id
     * @param array $data
     * @return object
     */
    public function updateContact( $id, array $data ) {
        return $this->request( 'PATCH', 'contacts/'.$id, [ 'contact' => $data ]);
    }

    /**
     * @param int $id
     * @return object
     */
    public function getInvoice( $id ) {
        return $this->request( 'GET', 'sales_invoices/'.$id );
    }

    /**
     * @param int $id
     * @param array $data
     * @return object
     */
    public function updateInvoice( $id, array $data ) {
        return $this->request( 'POST', 'sales_invoices/'.$id, [ 'sales_invoice' => $data ]);
    }


    /**
     * @param array $data
     * @return object
     */
    public function createInvoice( array $data ) {
        return $this->request( 'POST', 'sales_invoices', [ 'sales_invoice' => $data ]);
    }

    /**
     * @param int $invoiceId
     * @param array $data
     * @return object
     */
    public function createInvoiceSending( $invoiceId, array $data ) {
        return $this->request( 'PATCH', 'sales_invoices/' . $invoiceId . '/send_invoice', [ 'sales_invoice_sending' => $data ]);
    }

    /**
     * @param int $invoiceId
     * @param array $data
     * @return object
     */
    public function createInvoicePayment( $invoiceId, array $data ) {
        return $this->request( 'PATCH', 'sales_invoices/' . $invoiceId . '/register_payment', [ 'payment' => $data ]);
    }



    /**
     * @param string $method
     * @param string $resource
     * @param array|object $data
     *
     * @return object
     */
    private function request( $method, $resource, $data = array() ) {
        $url = $this->url . $resource . '.json';

        $response = $this->client->request( $method, $url, [
            'body' => json_encode( $data )
        ]);

        $body = $response->getBody();
        $data = json_decode( $body );
        return $data;
    }


}