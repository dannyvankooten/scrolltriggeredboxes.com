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

        // TODO: If user has moneybird_contact_id, update instead.

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

        $data = $this->post( 'contacts', [ 'contact' => $contact ]);
        $user->moneybird_contact_id = $data->id;
        return $user;
    }

    public function invoice( Payment $Payment ) {
        // TODO: Everything
    }

    /**
     * @param $data
     * @return object
     */
    public function createContact( $data ) {
        return $this->request( 'POST', 'contacts', $data );
    }

    /**
     * @param $id
     * @param $data
     * @return object
     */
    public function updateContact( $id, $data ) {
        return $this->request( 'PATCH', 'contacts/'.$id, $data );
    }

    /**
     * @param $data
     * @return object
     */
    public function createInvoice( $data ) {
        return $this->request( 'POST', 'sales_invoices', $data );
    }

    /**
     * @param $id
     * @param $data
     * @return object
     */
    public function updateInvoice( $id, $data ) {
        return $this->request( 'POST', 'sales_invoices/'.$id, $data );
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