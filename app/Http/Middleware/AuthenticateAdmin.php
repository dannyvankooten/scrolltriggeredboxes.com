<?php namespace App\Http\Middleware;

use Closure;

class AuthenticateAdmin {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if( ! in_array( $request->getClientIp(), config('auth.admins') ) ) {
			return response('Unauthorized.', 401);
		}

		return $next($request);
	}

}
