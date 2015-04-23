<?php namespace App\Http\Middleware;

use App\License;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class AuthenticateWithLicense {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next ) {
		$key = $request->server('PHP_AUTH_USER');
		$site = $request->server('PHP_AUTH_PW');
		$domain = parse_url( $site, PHP_URL_HOST );

		// key & site should be given
		if( ! $key || ! $site ) {
			return response( "You need a valid & activated license to access this resource.", 403 );
		}

		// find license
		$license = License::where('license_key', $key)->with('activations')->first();
		if( ! $license ) {
			return response( 'Your license key is invalid.', 403 );
		} elseif( $license->isExpired() ) {
			return response( 'Your license has expired.', 403 );
		}

		// find site
		$activation = $license->findDomainActivation( $domain );
		if( ! $activation ) {
			return response( sprintf( 'Your license was valid but it does not seem to be activated on %s.', $domain ), 403);
		}

		return $next($request);
	}

}
