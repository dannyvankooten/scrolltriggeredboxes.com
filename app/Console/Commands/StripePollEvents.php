<?php

namespace App\Console\Commands;

use App\Services\Payments\StripeAgent;
use Illuminate\Console\Command;
use Stripe;

class StripePollEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:poll-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Stripe for events the webhook may have missed.';


    /**
     * Execute the console command.
     * @param StripeAgent $agent
     */
    public function handle( StripeAgent $agent )
    {
        $eventTypes = [
            'charge.refunded',
            'invoice.payment_failed',
            'invoice.payment_succeeded',
        ];

        $events = Stripe\Event::all([
            'types' => $eventTypes,
            'created' => [
                'gte' => strtotime('-1 hour') // TODO: Make this configurable from CLI args
            ]
        ]);

        foreach( $events->data as $event ) {
            event($event); // fire local event
        }
    }



}
