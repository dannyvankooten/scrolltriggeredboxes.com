<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

class AuthenticateUser
{
	/**
	 * @var Guard
	 */
	protected $guard;

	/**
	 * @var Redirector
	 */
	protected $redirector;

	/**
	 * Admin constructor.
	 *
	 * @param Guard $guard
	 * @param Redirector $redirector
	 */
	public function __construct( Guard $guard, Redirector $redirector ) {
		$this->guard = $guard;
		$this->redirector = $redirector;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 *
	 * @return Response
	 */
	public function handle( Request $request, Closure $next )
	{
		if ($this->guard->guest()) {
			if ($request->ajax() || $request->wantsJson()) {
				return new Response('Unauthorized.', 401);
			} else {
				return $this->redirector->to('/login');
			}
		}

		return $next($request);
	}
}

