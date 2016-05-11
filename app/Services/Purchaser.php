<?php

namespace App\Services;

use App\Jobs\EmailLicenseDetails;
use App\User;
use App\License;
use App\Subscription;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DateTime;

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
     * @return User
     */
    public function user( User $user, $paymentToken )
    {
        $this->charger->customer($user, $paymentToken );
        $user->save();
        return $user;
    }

    /**
     * @param User $user
     * @param $quantity
     * @param $interval
     *
     * @return License
     */
    public function license( User $user, $quantity, $interval )
    {
        $discount_percentage = $quantity > 5 ? 30 : $quantity > 1 ? 20 : 0;
        $item_price = $interval == 'month' ? 5 : 50;

        // calculate amount based on number of activations & discount
        $amount = $item_price * $quantity;
        if( $discount_percentage > 0 ) {
            $amount = $amount * ( ( 100 - $discount_percentage ) / 100 );
        }

        // First, create license.
        $license = new License();
        $license->license_key = License::generateKey();
        $license->user()->associate( $user );
        $license->site_limit = $quantity;
        $license->expires_at = new DateTime("now");
        $license->save();

        // Then, create subscription
        $subscription = new Subscription([
            'interval' => $interval,
            'active' => 1,
            'next_charge_at' => new DateTime("now")
        ]);
        $subscription->amount = $amount;
        $subscription->license()->associate( $license );
        $subscription->user()->associate( $user );
        $subscription->save();

        // finally, charge subscription so that license starts
        $this->charger->subscription( $subscription );

        // dispatch job to send license details over email
        $this->dispatch( new EmailLicenseDetails( $license ) );

        return $license;
    }

}