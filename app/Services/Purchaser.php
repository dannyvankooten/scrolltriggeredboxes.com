<?php

namespace App\Services;

use App\User;
use App\License;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;


class Purchaser {

    use DispatchesJobs;

    /**
     * @param string $plan
     * @param string $interval
     *
     * @return float
     */
    public function calculatePrice( $plan, $interval )
    {
        $prices = array(
            'personal' => 6.00,
            'developer' => 20.00,
        );

        if( ! isset( $prices[ $plan ] ) ) {
            throw new \InvalidArgumentException( "Invalid plan ID: $plan" );
        }

        $price = $prices[ $plan ];
        $yearly = $interval === 'year';

        // a year costs 10 months (2 free months)
        if( $yearly ) {
            $price = $price * 10;
        }

        return $price;
    }

    /**
     * @param User $user
     * @param string $plan
     * @param string $interval
     *
     * @return License
     */
    public function license( User $user, $plan, $interval )
    {
        if( ! in_array( $plan, array( 'personal', 'developer' ) ) ) {
            throw new \InvalidArgumentException("Invalid plan ID: $plan");
        }

        $limits = [
            'personal' => 2,
            'developer' => 10
        ];
        $site_limit = $limits[ $plan ];

        // Create license.
        $license = new License();
        $license->expires_at = Carbon::now();
        $license->payment_method = $user->payment_method;
        $license->license_key = License::generateKey();
        $license->user_id = $user->id;
        $license->site_limit = $site_limit;
        $license->interval = $interval;
        $license->plan = $plan;

        return $license;
    }

}