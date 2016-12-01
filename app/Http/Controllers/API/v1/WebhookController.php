<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\Services\Payments\BraintreeAgent;
use Illuminate\Http\Request;
use Stripe;
use Braintree;
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
     * @param Request $request
     * @return Response
     */
    public function braintree( Request $request, BraintreeAgent $agent )
    {
        $notification = Braintree\WebhookNotification::parse($request->bt_signature, $request->bt_payload);

        // fire off local event
        event($notification);

        // tell Stripe we got this
        return new Response('', Response::HTTP_OK );
    }

}
