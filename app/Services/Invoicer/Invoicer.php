<?php

namespace App\Services\Invoicer;

use App\Services\TaxRateResolver;
use App\User;
use App\Payment;
use DateTime;
use Illuminate\Contracts\Cache\Repository as Cache;

class Invoicer {

    /**
     * @var Moneybird
     */
    protected $moneybird;

    /**
     * @var TaxRateResolver
     */
    protected $taxRateResolver;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Invoicer constructor.
     *
     * @param Moneybird $moneybird
     * @param TaxRateResolver $taxRateResolver
     * @param Cache $cache
     */
    public function __construct( Moneybird $moneybird, TaxRateResolver $taxRateResolver, Cache $cache = null ) {
        $this->moneybird = $moneybird;
        $this->taxRateResolver = $taxRateResolver;
        $this->cache = $cache;
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
        $taxRate = $this->resolveTaxRate( $payment );

        $invoiceData = [
            'contact_id' => $payment->user->moneybird_contact_id,
            'currency' => $payment->currency,
            'invoice_data' => $payment->created_at->format('Y-m-d'),
            'prices_are_incl_tax' => false,
            'details_attributes' => [
                [
                    'description' => 'Your Boxzilla subscription',
                    'price' => $payment->subtotal,
                    'tax_rate_id' => $taxRate->id,
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

    /**
     * Resolves a payment to a tax rate ID in Moneybird
     *
     * @param Payment $payment
     * @return object
     * @throws \Exception
     */
    public function resolveTaxRate( Payment $payment ) {

        if( $this->cache ) {
            $rates = $this->cache->get('moneybird.tax-rates');
        }

        if( empty( $rates ) ) {
            $rates = $this->moneybird->getTaxRates();

            if( $this->cache ) {
                $this->cache->put('moneybird.tax-rates', $rates, 120);
            }
        }

        $code = $this->taxRateResolver->getCodeForUser( $payment->user );

        foreach( $rates as $rate ) {
            if( strpos( $rate->name, '[' . $code . ']' ) !== false ) {
                return $rate;
            }
        }

        throw new \Exception( 'No valid tax rate found for code ' . $code );
    }


}