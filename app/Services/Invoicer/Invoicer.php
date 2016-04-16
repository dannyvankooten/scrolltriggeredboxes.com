<?php

namespace App\Services\Invoicer;

use App\User;
use App\Payment;
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

        $invoice = [
            'contact_id' => $payment->user->moneybird_contact_id,
            'currency' => $payment->currency,
            'invoice_data' => $payment->created_at->format('Y-m-d'),
            'prices_are_incl_tax' => false,
            'details_attributes' => [
                [
                    'description' => 'Your Boxzilla subscription',
                    'price' => $payment->subtotal,
                   // 'tax_rate_id' => '',
                ]
            ]

        ];

        if( $payment->moneybird_invoice_id ) {
            $this->updateInvoice( $payment->moneybird_invoice_id, $invoice );
        } else {
            $data = $this->createInvoice( $invoice );
            $payment->moneybird_invoice_id = $data->id;
        }

        return $payment;
    }

    /**
     * @param $data
     * @return object
     */
    public function createContact( $data ) {
        return $this->request( 'POST', 'contacts', [ 'contact' => $data ]);
    }

    /**
     * @param $id
     * @param $data
     * @return object
     */
    public function updateContact( $id, $data ) {
        return $this->request( 'PATCH', 'contacts/'.$id, [ 'contact' => $data ]);
    }

    /**
     * @param $data
     * @return object
     */
    public function createInvoice( $data ) {
        return $this->request( 'POST', 'sales_invoices', [ 'sales_invoice' => $data ]);
    }

    /**
     * @param $id
     * @param $data
     * @return object
     */
    public function updateInvoice( $id, $data ) {
        return $this->request( 'POST', 'sales_invoices/'.$id, [ 'sales_invoice' => $data ]);
    }

    /**
     * @param string $method
     * @param string $resource
     * @param array|object $data
     *
     * @return object
     */
    private function request( $method, $resource, $data ) {
        $url = $this->url . $resource . '.json';
        $response = $this->client->request( $method, $url, [
            'body' => json_encode( $data )
        ]);

        $body = $response->getBody();
        $data = json_decode( $body );
        return $data;
    }


}