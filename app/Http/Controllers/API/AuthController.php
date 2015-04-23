<?php namespace App\Http\Controllers\API;

use App\License, App\Activation;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller {

	/**
	 * Handle an authentication attempt.
	 *
	 * @return Response
	 */
	public function login( Request $request ) {

		$key = $request->server('PHP_AUTH_USER');
		$site = $request->server('PHP_AUTH_PW');
		$domain = parse_url( $site, PHP_URL_HOST );

		// key & site should be given
		if( ! $key || ! $site ) {
			return response( "Bad Request", 400 );
		}

		$data = [ 'success' => false ];

		// first, retrieve license key
		$license = License::where('license_key', $key)->with('activations')->first();
		if( ! $license ) {
			$data['message'] = sprintf( "The license key <code>%s</code> is invalid. Please check your purchase email for your correct license key.", $key );
			return response()->json($data);
		}

		// check if this site is already activated
		$activation = $license->findDomainActivation($domain);
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
				'url' => $site,
				'domain' => $domain
			]);
			$activation->license()->associate($license);
			$activation->save();
		}

		$data['message'] = sprintf( "Your license was activated, you have %d site activations left.", $license->getActivationsLeft() );
		$data['success'] = true;
		return response()->json( $data );
	}


	public function logout( Request $request ) {

		$key = $request->server('PHP_AUTH_USER');
		$site = $request->server('PHP_AUTH_PW');
		$domain = parse_url( $site, PHP_URL_HOST );

		// key & site should be given
		if( ! $key || ! $site ) {
			return response( "Bad Request", 400 );
		}

		$data = [
			'success' => true,
			'message' => 'Your license was successfully deactivated. You can use it on any other domain now.'
		];

		// first, retrieve license key
		$license = License::where('license_key', $key)->with('activations')->with('plugins')->first();
		if( ! $license ) {
			$data['message'] = "The license key seems to be invalid.";
			return response()->json( $data );
		}

		// now, delete activation (aka logout)
		$activation = $license->findDomainActivation( $domain );
		if( $activation ) {
			$activation->delete();
		}

		return response()->json( $data );
	}

}