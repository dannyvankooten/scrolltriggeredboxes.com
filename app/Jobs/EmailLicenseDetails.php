<?php

namespace App\Jobs;

use App\License;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailLicenseDetails extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var License
     */
    protected $license;

    /**
     * Create a new job instance.
     *
     * @param License $license
     */
    public function __construct( License $license )
    {
        $this->license = $license;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     */
    public function handle( Mailer $mailer)
    {
        $license = $this->license;
        $user = $this->license->user;

        $mailer->send('emails.license-details', ['license' => $license, 'user' => $user ], function ($m) use( $user, $license ) {
            $m->to($user->email, $user->name)->subject('Your license');
        });
    }
}