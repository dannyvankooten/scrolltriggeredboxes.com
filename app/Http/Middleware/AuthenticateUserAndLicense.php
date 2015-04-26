<?php namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class AuthenticateUserAndLicense extends AuthenticateUser {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		 /* @var User */
		$user = $this->auth->user();
		if( ! $user->hasValidLicense() ) {
			return response( "401 Unauthorized", 401 );
		}

		return $next($request);
	}

}
