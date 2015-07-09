<?php namespace App\Http\Middleware;

use Closure;

class AuthenticateAdmin {

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
	public function __construct()
	{

	}

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
