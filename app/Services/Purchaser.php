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
     * @param float $quantity
     * @param float $interval
     *
     * @return int
     */
    public function calculatePrice( $quantity, $interval ) {
        $base_price = $interval == 'month' ? 4 : 40;
        $unit_price = 0.5 * $base_price;
        $amount = $base_price + ( $unit_price * $quantity );
        $amount = round( $amount, 2 );
        return $amount;
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
        // subtotal
        $amount = $this->calculatePrice( $quantity, $interval );

        // charge user
        $payment = $this->charger->charge( $user, $amount );

        // Create license.
        $license = new License();
        $license->license_key = License::generateKey();
        $license->user_id = $user->id;
        $license->site_limit = $quantity;
        $license->expires_at = new DateTime("+1 $interval");
        $license->save();

        // Create subscription
        $subscription = new Subscription([
            'interval' => $interval,
            'active' => 1,
            'next_charge_at' => $license->expires_at->modify('-5 days')
        ]);
        $subscription->amount = $amount;
        $subscription->license_id = $license->id;
        $subscription->user_id = $user->id;
        $subscription->save();

        // Attach payment to subscription
        $payment->subscription_id = $subscription->id;
        $payment->save();

        // dispatch job to send license details over email
        $this->dispatch( new EmailLicenseDetails( $license ) );

        return $license;
    }

}