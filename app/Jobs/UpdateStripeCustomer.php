<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\Payments\Charger;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateStripeCustomer extends Job implements ShouldQueue
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
     * @param Charger $charger
     *
     * @return void
     */
    public function handle( Charger $charger )
    {
        if( $this->user->stripe_customer_id ) {
            $charger->customer( $this->user );
        }
    }
}
