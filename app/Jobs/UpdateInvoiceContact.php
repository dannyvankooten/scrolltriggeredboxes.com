<?php

namespace App\Jobs;

use App\User;
use App\Services\Invoicer\Invoicer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateInvoiceContact extends Job implements ShouldQueue
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
     * @param Invoicer $invoicer
     */
    public function handle( Invoicer $invoicer )
    {
        $invoicer->contact( $this->user, true );
    }
}
