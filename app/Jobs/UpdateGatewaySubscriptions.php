<?php

namespace App\Jobs;

use App\Services\Payments\Agent;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateGatewaySubscriptions extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct( User $user )
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param Agent $agent
     *
     * @return void
     */
    public function handle( Agent $agent )
    {
        $licenses = $this->user->getActiveLicenses();

        foreach( $licenses as $license ) {
            // re-create each active subscription in stripe with new tax rate.
            $agent->createSubscription($license);

            // save changes
            $license->save();
        }

    }
}
