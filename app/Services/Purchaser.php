<?php

namespace App\Services;

use App\Jobs\EmailLicenseDetails;
use App\User;
use App\License;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DateTime;
use App\Services\Payments\StripeAgent;

class Purchaser {

    use DispatchesJobs;

    /**
     * @var StripeAgent
     */
    protected $agent;

    /**
     * Purchaser constructor.
     *
     * @param StripeAgent $agent
     */
    public function __construct( StripeAgent $agent )
    {
        $this->agent = $agent;
    }

    /**
     * @param User $user
     * @param string $paymentToken
     *
     * @return User
     */
    public function user( User $user, $paymentToken )
    {
        $user = $this->agent->updatePaymentMethod($user, $paymentToken );
        $user->save();
        return $user;
    }

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

        $limits = array(
            'personal' => 2,
            'developer' => 10
        );
        $site_limit = $limits[ $plan ];

        // Create license.
        $license = new License();
        $license->license_key = License::generateKey();
        $license->user_id = $user->id;
        $license->site_limit = $site_limit;
        $license->interval = $interval;
        $license->plan = $plan;

        // setup subscription
        $this->agent->createSubscription( $license );

        // save license
        $license->save();

        // dispatch job to send license details over email
        $this->dispatch(new EmailLicenseDetails($license));

        return $license;
    }

}