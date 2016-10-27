<?php

namespace App\Services;

use App\Jobs\EmailLicenseDetails;
use App\User;
use App\License;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DateTime;
use App\Services\Payments\Charger;

class Purchaser {

    use DispatchesJobs;

    /**
     * @var Charger
     */
    protected $charger;

    /**
     * @var SubscriptionAgent
     */
    protected $agent;

    /**
     * Purchaser constructor.
     *
     * @param Charger $charger
     * @param SubscriptionAgent $agent
     */
    public function __construct( Charger $charger, SubscriptionAgent $agent )
    {
        $this->charger = $charger;
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
        $user = $this->charger->customer($user, $paymentToken );
        $user->save();
        return $user;
    }

    /**
     * @param string $plan
     * @param string $interval
     *
     * @return float
     */
    public function calculatePrice( $plan, $interval ) {
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
        $license->auto_renews = true;
        $license->interval = $interval;
        $license->plan = $plan;

        // setup subscription
        $this->agent->create( $license );

        // save license
        $license->save();

        // dispatch job to send license details over email
        $this->dispatch(new EmailLicenseDetails($license));

        return $license;
    }

}