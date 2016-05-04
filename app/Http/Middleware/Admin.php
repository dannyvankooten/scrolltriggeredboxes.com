<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Auth\Guard;

class Admin {

	/**
	 * @var Guard
	 */
	protected $guard;

	/**
	 * Admin constructor.
	 *
	 * @param Guard $guard
	 */
	public function __construct( Guard $guard ) {
		$this->guard = $guard;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @return Response
	 */
	public function handle( Request $request, Closure $next )
	{
		if ( $this->guard->check() && $this->guard->user()->isAdmin() )
		{
            return $next($request);
        }

		return new Response('Unauthorized.', 401);
	}

}
