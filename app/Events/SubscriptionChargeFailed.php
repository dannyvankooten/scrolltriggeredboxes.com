<?php

namespace App\Events;

use App\Events\Event;
use App\Subscription;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SubscriptionChargeFailed extends Event
{
    use SerializesModels;

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * Create a new event instance.
     *
     * @param Subscription $subscription
     */
    public function __construct( Subscription $subscription )
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    /**
     * @return Subscription
     */
    public function getSubscription() {
        return $this->subscription;
    }
}
