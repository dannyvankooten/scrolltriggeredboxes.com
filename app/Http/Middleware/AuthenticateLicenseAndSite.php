<?php namespace App\Http\Middleware;

use App\License;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class AuthenticateLicenseAndSite extends AuthenticateLicense {


	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next ) {

		// no need to check for site if user already authenticated
		if( $this->auth->user() ) {
			return $next($request);
		}

		// only do our own stuff if next is next
		$return = parent::handle( $request, $next );
		if( $return != $next ) {
			return $return;
		}

		// find site
		$activation = $request->license->findDomainActivation( $request->domain );
		if( ! $activation ) {
			return response()->json([
				'error' => [
					'message' => sprintf( 'Your license is valid but it does not seem to be activated on %s.', $request->domain ),
				]
			], 401 );
		}

		return $next($request);
	}

}
