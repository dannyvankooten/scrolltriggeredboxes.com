<?php namespace App\Http\Middleware;

use App\Services\LicenseGuard;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticateLicense  {

	/**
	 * @var LicenseGuard
	 */
	protected $guard;

	/**
	 * Admin constructor.
	 *
	 * @param LicenseGuard $guard
	 */
	public function __construct( LicenseGuard $guard ) {
		$this->guard = $guard;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request
	 * @param  Closure  $next
	 *
	 * @return JsonResponse
	 */
	public function handle( Request $request, Closure $next) {

		if( $this->guard->guest() ) {
			return new JsonResponse([
				'error' => [
					'message' => $this->guard->getErrorMessage()
				]
			], 401 );
		}

		return $next($request);
	}



}
