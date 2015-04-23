<?php namespace App\Http\Controllers\API;

use App\Http\Requests\APIRequest;
use App\License, App\Activation;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller {

	public function __construct() {
		$this->middleware( 'auth.license' );
	}

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function login( Request $request ) {

		$data = [ 'success' => false ];
		$license = $request->license;

		// check if this site is already activated
		$activation = $license->findDomainActivation($request->domain);
		if( $activation ) {
			// already logged-in
			$activation->touch();
		} elseif( $license->isExpired() ) {
			$data['message'] = sprintf( "Your license is has expired.", $license->site_limit );
			return response()->json( $data );
		} elseif( $license->isAtSiteLimit() ) {
			$data['message'] = sprintf( "Your license is at its activation limit of %d sites.", $license->site_limit );
			return response()->json( $data );
		} else {
			// finally, activate site (aka login)
			$activation = new Activation([
				'url' => $request->site,
				'domain' => $request->domain
			]);
			$activation->license()->associate($license);
			$activation->save();
		}

		$data['message'] = sprintf( "Your license was activated, you have %d site activations left.", $license->getActivationsLeft() );
		$data['success'] = true;
		return response()->json( $data );
	}


	public function logout( Request $request ) {

		$data = [
			'success' => true,
			'message' => 'Your license was successfully deactivated. You can use it on any other domain now.'
		];

		$license = $request->license;

		// now, delete activation (aka logout)
		$activation = $license->findDomainActivation( $request->domain );
		if( $activation ) {
			$activation->delete();
		}

		return response()->json( $data );
	}

}