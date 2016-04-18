<?php

namespace App\Services\Invoicer;

use GuzzleHttp\Client;

class Moneybird {

    /**
     * Moneybird constructor.
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
     * @param int $invoiceId
     * @return object
     */
    public function createCreditInvoice( $invoiceId ) {
        return $this->request( 'PATCH', 'sales_invoices/'. $invoiceId .'/duplicate_creditinvoice' );
    }

    /**
     * @param string $method
     * @param string $resource
     * @param array|object $data
     *
     * @return object
     */
    public function request( $method, $resource, $data = array() ) {
        $url = $this->url . $resource . '.json';

        $response = $this->client->request( $method, $url, [
            'body' => json_encode( $data )
        ]);

        $body = $response->getBody();
        $data = json_decode( $body );
        return $data;
    }


}