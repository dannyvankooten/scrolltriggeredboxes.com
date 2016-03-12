<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Guard;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
Use App\License;

class AuthenticateLicense  {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {

		// no need to check for license if user already authenticated
		if( $this->auth->user() ) {
			return $next($request);
		}

		$key = urldecode( $request->server('PHP_AUTH_PW') );
		$site = urldecode( $request->server('PHP_AUTH_USER') );

		if( empty( $key ) || empty( $site ) ) {
			// no license key or site given
			return response()->json([
				'error' => [
					'message' => 'Please provide your license key and site URL.'
				]
			], 400 );
		}

		// find license
		$license = License::where('license_key', $key)->with('activations')->first();
		if( ! $license ) {
			// license key was not found
			return response()->json([
				'error' => [
					'message' => sprintf( "Your license seems to be invalid. Please check <a href=\"%s\">your account</a> for the correct license key.", url( '/account' ) )
				]
			], 401 );
		}

		if( $license->isExpired() ) {
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
