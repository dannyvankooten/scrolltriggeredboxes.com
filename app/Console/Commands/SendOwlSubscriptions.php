<?php

namespace App\Console\Commands;

use App\Events\SubscriptionChargeFailed;
use App\License;
use App\Subscription;
use Illuminate\Console\Command;
use DateTime;
use Exception;

class SendOwlSubscriptions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:add_from_sendowl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup subscriptions for licenses that have none.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var License[] $licenses */
        $licenses = License::with([ 'subscription', 'user'])->get();
        $migrationDate = new DateTime( '2016-05-10 00:00:00' );
        $count = 0;

        foreach( $licenses as $license ) {

            // skip licenses that already have a subscription
            if( $license->subscription || $license->created_at > $migrationDate ) {
                continue;
            }

            // Create subscription
            $subscription = new Subscription([
                'interval' => 'year',
                'active' => 1,
                'next_charge_at' => $license->expires_at->modify('-5 days')
            ]);

            // set subscription amount to old sendowl prices
            $subscription->amount = $license->site_limit > 1 ? 49.5 : 29.5;
            $subscription->license_id = $license->id;
            $subscription->user_id = $license->user->id;
            $subscription->save();

            $this->info( sprintf( 'Setting up subscription of %s for %s', $subscription->amount, $license->user->email ) );
            $count++;
        }

        $this->info( sprintf( "Success! Created %d subscriptions.", $count ) );
    }
}
