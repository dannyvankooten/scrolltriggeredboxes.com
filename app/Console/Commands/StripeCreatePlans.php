<?php

namespace App\Console\Commands;

use App\License;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

use Illuminate\Contracts\Mail\Mailer;
use Stripe;

class StripeCreatePlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:create-subscription-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates subscription plans in Stripe.';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stripeSecret = config('services.stripe.secret');
        Stripe\Stripe::setApiKey( $stripeSecret );

        $defaults = [
            'currency' => 'usd',
        ];
        $plans = [];

        // the core plans (current)
        $plans[] = [
            'id' => 'boxzilla-personal-monthly',
            'name' => 'Boxzilla Personal (monthly)',
            'amount' => 600,
            'interval' => 'month',
        ];
        $plans[] = [
            'id' => 'boxzilla-personal-yearly',
            'name' => 'Boxzilla Personal (yearly)',
            'amount' => 6000,
            'interval' => 'year',
        ];
        $plans[] = [
            'id' => 'boxzilla-developer-monthly',
            'name' => 'Boxzilla Developer (monthly)',
            'amount' => 2000,
            'interval' => 'month',
        ];
        $plans[] = [
            'id' => 'boxzilla-developer-yearly',
            'name' => 'Boxzilla Developer (yearly)',
            'amount' => 20000,
            'interval' => 'year',
        ];

        // the legacy plans
        $sitePrices = [
            3 => 10,
            4 => 12,
            5 => 14,
            6 => 16,
            7 => 18
        ];
        foreach( $sitePrices as $numberOfSites => $price ) {
            $plans[] = [
                'id' => sprintf( 'boxzilla-2016-%d-sites-monthly', $numberOfSites ),
                'name' => sprintf( 'Boxzilla %s sites (legacy 2016, monthly)', $numberOfSites ),
                'amount' => $price * 100,
                'interval' => 'month',
            ];

            $plans[] = [
                'id' => sprintf( 'boxzilla-2016-%d-sites-yearly', $numberOfSites ),
                'name' => sprintf( 'Boxzilla %s sites (legacy 2016, yearly)', $numberOfSites ),
                'amount' => $price * 100 * 10,
                'interval' => 'year',
            ];
        }

        foreach( $plans as $plan ) {
            $plan = array_merge( $defaults, $plan );

            // try to delete the plan first
            try {
                $stripePlan = Stripe\Plan::retrieve($plan['id']);
                $stripePlan->delete();
            } catch( Stripe\Error\Base $e ) {
                // do nothing
            }

            // create new updated plan.
            Stripe\Plan::create($plan);
        }

    }



}
