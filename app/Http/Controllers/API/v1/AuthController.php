<?php namespace App\Http\Controllers\API\v1;

use App\Activation;
use App\License;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller {

	/**
	 * AuthController constructor.
	 */
	public function __construct() {
		$this->middleware( [ 'throttle', 'auth.license' ] );
	}

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function login( Request $request ) {

		/** @var License $license */
		$license = $request->license;

		// check if license is expired
		if( $license->isExpired() ) {
			return response()->json([
				'error' => [
					'message' => sprintf( "Your license has expired.", $license->site_limit )
				]
			]);
		}

		// check if this site is already activated
		$activation = $license->findDomainActivation($request->domain);

		if( ! $activation ) {

			// check if license is at limit
			if( $license->isAtSiteLimit() ) {
				return response()->json([
					'error' => [
						'message' => sprintf( "Your license is at its activation limit of %d sites.", $license->site_limit )
					]
				]);
			}

			// activate license on given site
			$activation = new Activation([
				'url' => $request->site,
				'domain' => $request->domain
			]);
			$activation->license()->associate($license);

			Log::info( "Activated license #{$license->id} on {$request->domain}" );
		}

		$activation->touch();
		$activation->save();

		return response()->json([
			'data' => [
				'message' => sprintf( "Your license was activated, you have %d site activations left.", $license->getActivationsLeft() )
			],

		]);
	}


	public function logout( Request $request ) {

		$license = $request->license;

		// now, delete activation (aka logout)
		$activation = $license->findDomainActivation( $request->domain );
		if( $activation ) {
			Log::info( "Deactivated license #{$license->id} on {$request->domain}" );
			$activation->delete();
		}

		return response()->json([
			'data' => [
				'message' => 'Your license was successfully deactivated. You can use it on any other domain now.'
			]
		]);
	}

}