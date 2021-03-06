<?php

namespace App\Services\Invoicer;

use App\Services\TaxRateResolver;
use App\User;
use App\Payment;
use DateTime;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Logging\Log;

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
     * @var Log
     */
    protected $log;

    /**
     * Invoicer constructor.
     *
     * @param Moneybird $moneybird
     * @param TaxRateResolver $taxRateResolver
     * @param Cache $cache
     * @param Log $log
     */
    public function __construct( Moneybird $moneybird, TaxRateResolver $taxRateResolver, Cache $cache = null, Log $log = null ) {
        $this->moneybird = $moneybird;
        $this->taxRateResolver = $taxRateResolver;
        $this->cache = $cache;
        $this->log = $log;
    }

    /**
     * @param User $user
     */
    public function updateContact( User $user ) {

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

        if( $this->hasContact($user) ) {
            $data = $this->moneybird->updateContact( $user->moneybird_contact_id, $contact );
        } else {
            $data = $this->moneybird->createContact( $contact );
        }

        $user->moneybird_contact_id = $data->id;

        $this->log && $this->log->info(sprintf('Updated MoneyBird contact for user %s.', $user->email));
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    private function hasContact( User $user ) {
        return ! empty($user->moneybird_contact_id);
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
     *
     * TODO: Include subscription period in description
     */
    public function updateInvoice( Payment $payment ) {
        $taxRate = $this->resolveTaxRate( $payment );

        $invoiceData = [
            'contact_id' => $payment->user->moneybird_contact_id,
            'currency' => $payment->getCurrency(),
            'invoice_date' => $payment->created_at->format('Y-m-d'),
            'prices_are_incl_tax' => false,
            'details_attributes' => [
                [
                    'description' => 'Your Boxzilla subscription',
                    'price' => $payment->subtotal,
                    'tax_rate_id' => $taxRate->id,
                ]
            ]
        ];
        
        if( $this->hasInvoice($payment) ) {
            // invoice exists, update it
            $this->moneybird->updateInvoice( $payment->moneybird_invoice_id, $invoiceData );
        } else {
            // create new invoice
            $data = $this->moneybird->createInvoice( $invoiceData );

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

            // store moneybird id
            $payment->moneybird_invoice_id = $data->id;
        }

        $this->log && $this->log->info(sprintf('Updated MoneyBird invoice for payment %s.', $payment->id));
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
            if( stripos( $rate->name, $code ) !== false ) {
                return $rate;
            }
        }

        throw new \Exception( 'No valid tax rate found for code ' . $code );
    }


}