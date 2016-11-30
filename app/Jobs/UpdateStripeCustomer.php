<?php

namespace App\Jobs;

use App\Services\Payments\StripeAgent;
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
     * @param StripeAgent $agent
     *
     * @return void
     */
    public function handle( StripeAgent $agent )
    {
        if( ! empty($this->user->stripe_customer_id) ) {
            $agent->updatePaymentMethod($this->user);
        }
    }
}
