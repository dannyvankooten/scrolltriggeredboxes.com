<?php namespace App\Http\Middleware;

use App\Services\LicenseGuard;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticateLicense  {

	/**
	 * @var LicenseGuard
	 */
	protected $auth;

	/**
	 * Admin constructor.
	 *
	 * @param LicenseGuard $auth
	 */
	public function __construct( LicenseGuard $auth ) {
		$this->auth = $auth;
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

		$license = $this->auth->license();

		if( ! $license ) {
			return new JsonResponse([
				'error' => [
					'message' => $this->auth->getErrorMessage(),
					'code' => $this->auth->getErrorCode(),
				]
			], 401 );
		}

		return $next($request);
	}



}
