<?php

namespace App\Jobs;

use App\Services\Payments\Agent;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateGatewayCustomer extends Job implements ShouldQueue
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
        $agent->updatePaymentMethod($this->user);
        $this->user->save();
    }
}
