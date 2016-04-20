<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class RedirectIfAuthenticated
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
	public function handle(Request $request, Closure $next)
	{
		if ($this->guard->check()) {
			return $this->redirector->to('/');
		}

		return $next($request);
	}
}