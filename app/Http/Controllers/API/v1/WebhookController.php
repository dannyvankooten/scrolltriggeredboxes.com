<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\Services\Payments\Agent;
use Stripe;
use Braintree;

use Illuminate\Http\Response;
use Illuminate\Http\Request;

class WebhookController extends Controller {

    public function __construct( Agent $agent ) {
        // instantiate agent so that creds are set
    }

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
    public function braintree()
    {
        if( empty( $_POST['bt_signature'] ) || empty( $_POST['bt_payload'] ) ) {
            return new Response('', Response::HTTP_BAD_REQUEST );
        }

        $notification = Braintree\WebhookNotification::parse($_POST['bt_signature'], $_POST['bt_payload']);

        // fire off local event
        event($notification);

        // tell Braintree we got this
        return new Response('', Response::HTTP_OK );
    }

}
