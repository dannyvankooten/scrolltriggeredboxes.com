<?php namespace App\Http\Controllers\API\v1;

use App\Events\UserCreated;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\CreateUser;
use App\Jobs\PurchasePlan;
use DB, App\License, App\User, App\Activation, App\Plan, App\Plugin;

use HelpScoutApp\DynamicApp as HelpScoutApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;

class HelpScoutController extends Controller {

	/**
	 * Create a new license key for a new SendOwl order or add plugin access to an existing one (for bundle orders)
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function get( Request $request)
	{

		$helpscoutApp = new HelpScoutApp( config('services.helpscout')['secret'] );

		if(env('VERIFY_SIGNATURES', true)) {
			if( ! $helpscoutApp->isSignatureValid() ) {
				abort( 403 );
			}
		}

		$customer = $helpscoutApp->getCustomer();
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
