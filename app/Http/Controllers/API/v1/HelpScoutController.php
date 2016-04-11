<?php namespace App\Http\Controllers\API\v1;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

use HelpScoutApp\DynamicApp as HelpScoutApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HelpScoutController extends Controller {

	/**
	 * HelpScoutController constructor.
	 */
	public function __construct(  ) {
		$this->middleware('helpscout.signature');
	}

	/**
	 * Create a new license key for a new SendOwl order or add plugin access to an existing one (for bundle orders)
	 *
	 * @return Response
	 */
	public function get( HelpScoutApp $helpscout )
	{
		$customer = $helpscout->getCustomer();
		$email = $customer->getEmail();
		$user = User::where('email', $email)->first();

		if( $user ) {
			$html = view( 'helpscout.customer', [ 'user' => $user ])->render();
		} else {
			$html = sprintf( '<p>No license found for <strong>%s</strong>.</p>', $email );
		}

		return new JsonResponse([ 'html' => $html]);
	}

}
