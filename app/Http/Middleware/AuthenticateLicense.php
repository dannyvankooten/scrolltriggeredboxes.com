<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
Use App\License;

class AuthenticateLicense  {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$key = urldecode( $request->server('PHP_AUTH_PW') );
		$site = urldecode( $request->server('PHP_AUTH_USER') );

		if( ! $key || ! $site ) {
			// no license key or site given
			return response()->json([
				'error' => [
					'message' => 'Please provide your license key and site URL.'
				]
			], 400 );
		}

		$license = License::where('license_key', $key)->with('activations')->first();
		if( ! $license ) {
			// license key was not found
			return response()->json([
				'error' => [
					'message' => sprintf( "Your license seems to be invalid. Please check <a href=\"%s\">your account</a> for the correct license key.", url( '/account' ) )
				]
			], 401 );
		} elseif( $license->isExpired() ) {
			// license has expired
			return response()->json([
				'error' => [
					'message' => sprintf( "Your license expired on %s.", $license->expires_at->format('F j, Y') )
				]
			], 401 );
		}

		// todo: add check for revoked licenses (refunds, disputes, etc..)

		$request->license = $license;
		$request->site = $site;

		// parse domain from site url
		$domain = parse_url( $site, PHP_URL_HOST );
		$request->domain = $domain;

		return $next($request);
	}

}
