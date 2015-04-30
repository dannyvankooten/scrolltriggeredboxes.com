<?php namespace App\Http\Middleware;

use App\License;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class AuthenticateLicenseAndSite {


	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next ) {

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
