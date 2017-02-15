<?php namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\Services\Payments\Agent;
use Stripe;
use Braintree;

use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class WebhookController extends Controller {

    /**
     * @var Log
     */
    private $log;

    /**
     * WebhookController constructor.
     * 
     * @param Agent $agent We inject this so that credentials are set.
     * @param Log $log
     */
    public function __construct( Agent $agent, Log $log ) {
        $this->log = $log;
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

        // log received webhook event
        $this->log->info(sprintf("Stripe event received: %s", $event->type));

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

        // parse webhook notification
        $notification = Braintree\WebhookNotification::parse($_POST['bt_signature'], $_POST['bt_payload']);

        // log received webhook event
        $this->log->info(sprintf("Braintree event received: %s", $notification->kind));

        // fire off local event
        event($notification);

        // tell Braintree we got this
        return new Response('', Response::HTTP_OK );
    }

}
