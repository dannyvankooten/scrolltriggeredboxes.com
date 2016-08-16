<?php

namespace App\Services;

use App\Jobs\EmailLicenseDetails;
use App\Services\Payments\Broker;
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
     * @param float $interval
     *
     * @return int
     */
    public function calculatePrice( $plan, $interval ) {
        $planPrices = array(
            "personal" => 6,
            "developer" => 10,
            "agency" => 24,
        );

        $price = $planPrices[ $plan ];
        $isYearly = $interval === 'year';
        $total = $isYearly ? $price * 10 : $price;
        return $total;
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
        $planLimits = array(
            "personal" => 1,
            "developer" => 3,
            "agency" => 10,
        );

        // subtotal
        $amount = $this->calculatePrice( $plan, $interval );
        $payment = null;

        // charge user
        $payment = $this->charger->charge( $user, $amount );

        // Create license.
        $license = new License();
        $license->license_key = License::generateKey();
        $license->user_id = $user->id;
        $license->site_limit = $planLimits[$plan];
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