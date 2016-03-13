<?php namespace App\Http\Controllers\API\v1;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

use HelpScoutApp\DynamicApp as HelpScoutApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HelpScoutController extends Controller {

	/**
	 * @var HelpScoutApp
	 */
	protected $helpscout;

	/**
	 * HelpScoutController constructor.
	 */
	public function __construct( HelpScoutApp $helpscout ) {
		$this->helpscout = $helpscout;
		$this->middleware('helpscout.signature');
	}

	/**
	 * Create a new license key for a new SendOwl order or add plugin access to an existing one (for bundle orders)
	 *
	 * @return Response
	 */
	public function get()
	{
		$customer = $this->helpscout->getCustomer();
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
