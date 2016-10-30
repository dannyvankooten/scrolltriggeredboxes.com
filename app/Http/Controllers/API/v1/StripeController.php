<?php namespace App\Http\Controllers\API\v1;

use App\Events\StripeEvent;
use App\Http\Controllers\Controller;
use App\Services\Payments\StripeAgent;

use Stripe;
use Illuminate\Http\Response;

class StripeController extends Controller {

    /**
     * @param StripeAgent $agent
     *
     * @return Response
     */
	public function get( StripeAgent $agent )
	{
        // retrieve the request's body and parse it as JSON
        $input = @file_get_contents("php://input");
        $eventData = json_decode($input);

        // verify the event by fetching it from Stripe
        $event = Stripe\Event::retrieve($eventData->id);

        // fire off local event
        event($event);

        // tell Stripe we got this
        return new Response('', Response::HTTP_OK );
	}

}
