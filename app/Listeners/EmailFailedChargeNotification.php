<?php

namespace App\Listeners;

use App\Events\SubscriptionChargeFailed;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailFailedChargeNotification implements ShouldQueue
{

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * Create the event listener.
     *
     * @param Mailer $mailer
     */
    public function __construct( Mailer $mailer )
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  SubscriptionChargeFailed  $event
     * @return void
     */
    public function handle(SubscriptionChargeFailed $event)
    {
        $subscription = $event->getSubscription();
        $user = $subscription->user;
        $this->mailer->send( 'emails.charge-failed', [ 'subscription' => $subscription ], function( $email ) use( $event, $user ) {
            /**
             * @var \Illuminate\Mail\Message $email
             */
           $email
               ->to( $user->email, $user->name )
               ->subject( 'Boxzilla - Payment Failure' );
        });
    }
}
