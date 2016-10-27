<?php

namespace App\Services;

use App\Jobs\EmailLicenseDetails;
use App\User;
use App\License;
use App\Subscription;
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
     * Purchaser constructor.
     *
     * @param Charger $charger
     */
    public function __construct( Charger $charger )
    {
        $this->charger = $charger;
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

        // subscribe user to plan in Stripe
        $plan_id = sprintf( 'boxzilla-%s-%sly', $plan, $interval );
        $subscription = \Stripe\Subscription::create([
            'customer' => $user->stripe_customer_id,
            'plan' => $plan_id
        ]);

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
        $license->expires_at = new DateTime("+1 $interval");
        $license->auto_renews = true;
        $license->stripe_subscription_id = $subscription->id;
        $license->interval = $interval;
        $license->save();

        // dispatch job to send license details over email
        $this->dispatch( new EmailLicenseDetails( $license ) );

        return $license;
    }

}