<?php

namespace App\Services;

use App\User;
use DvK\Laravel\Vat\Facades\Rates as VatRates;

class TaxRateResolver {
    
    /**
     * @param User $user
     * @return string
     */
    public function getCodeForUser( User $user ) {
        // no tax for non-EU customers
        if( ! $user->inEurope() ) {
            return 'NO VAT';
        }

        // Dutch tax for all NL customers
        if( $user->country === 'NL' ) {
            return 'NL STANDARD';
        }

        // Reverse charge for EU businesses (outside of NL)
        if( ! empty( $user->vat_number ) ) {
            return 'REVERSE CHARGED';
        }

        // EU tax rate of specific country otherwise
        return $user->country . ' STANDARD';
    }

    /**
     * @param string $code
     *
     * @return int
     */
    public function getRateForCode( $code ) {
       static $map = [
           'NO VAT' => 0,
           'REVERSE CHARGED' => 0
       ];

        if( isset( $map[ $code ] ) ) {
            return $map[ $code ];
        }

        $country = strtoupper( substr( $code, 0, 2 ) );
        $rate = strtolower( substr( $code, 3 ) );

        return VatRates::country( $country, $rate );
    }

    /**
     * @param User $user
     * @return int
     */
    public function getRateForUser( User $user ) {
        $code = $this->getCodeForUser( $user );
        return $this->getRateForCode( $code );
    }

}