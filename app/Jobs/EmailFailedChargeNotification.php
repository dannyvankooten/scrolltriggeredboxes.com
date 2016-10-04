<?php

namespace App\Jobs;

use App\Events\SubscriptionChargeFailed;
use App\Subscription;
use Illuminate\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmailFailedChargeNotification extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * Create the event listener.
     *
     * @param Subscription $subscription
     */
    public function __construct( Subscription $subscription )
    {
        $this->subscription = $subscription;
        $this->onQueue('emails');
    }

    /**
     * Handle the event.
     *
     * @param Mailer $mailer
     */
    public function handle( Mailer $mailer)
    {
        $subscription = $this->subscription;
        $user = $subscription->user;

        $mailer->send( 'emails.charge-failed', [ 'subscription' => $subscription ], function( $email ) use( $event, $user ) {
            /**
             * @var \Illuminate\Mail\Message $email
             */
           $email
               ->to( $user->email, $user->name )
               ->subject( 'Boxzilla Plugin - Payment Failure' );
        });

    }
}
