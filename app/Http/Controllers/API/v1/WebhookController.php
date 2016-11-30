<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\Services\Payments\PayPalEvent;
use Stripe;
use Illuminate\Http\Response;

class WebhookController extends Controller {

    /**
     * @return Response
     */
	public function stripe()
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

    /**
     * @return Response
     */
    public function paypal()
    {
        // retrieve the request's body and parse it as JSON
        $input = @file_get_contents("php://input");
        $eventData = json_decode($input);

        $event = new PayPalEvent($eventData);

        // fire off local event
        event($event);

        // tell Stripe we got this
        return new Response('', Response::HTTP_OK );
    }

}
