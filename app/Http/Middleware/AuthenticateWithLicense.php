<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
Use App\License;

class AuthenticateWithLicense  {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$key = $request->server('PHP_AUTH_USER');
		$site = $request->server('PHP_AUTH_PW');

		if( ! $key || ! $site ) {
			return response()->json( [ 'message' => "Please provide your license key and site.", 'code' => 400 ], 400 );
		}

		// find license
		$license = License::where('license_key', $key)->with('activations')->first();
		if( ! $license ) {
			return response()->json( [ 'message' => "Your license seems to be invalid. Please check your purchase email for the correct license key.", 'code' => 403 ], 403 );
		} elseif( $license->isExpired() ) {
			return response( [ 'message' => "Your license has expired.", 'code' => 403 ], 403 );
		}

		$request->license = $license;
		$request->site = $site;
		$domain = parse_url( $site, PHP_URL_HOST );
		$request->domain = $domain;

		return $next($request);
	}

}
