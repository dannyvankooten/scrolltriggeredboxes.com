<?php namespace App\Http\Middleware;

use App\Services\LicenseGuard;
use Closure;
Use App\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateLicense  {

	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
	public function handle( Request $request, Closure $next) {

		/** @var LicenseGuard $guard */
		$guard = Auth::guard('api');

		if( $guard->guest() ) {
			return response()->json([
				'error' => [
					'message' => $guard->getErrorMessage()
				]
			], 401 );
		}

		return $next($request);
	}



}
