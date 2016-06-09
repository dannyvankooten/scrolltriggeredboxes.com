<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * These middleware are run during every request to your application.
	 *
	 * @var array
	 */
	protected $middleware = [
		Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
	];
	/**
	 * The application's route middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [
		'web' => [
			Middleware\EncryptCookies::class,
			Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			Illuminate\Session\Middleware\StartSession::class,
			Illuminate\View\Middleware\ShareErrorsFromSession::class,
			Middleware\VerifyCsrfToken::class,
		],
		'api' => [
			Middleware\SetApiAccessControlHeaders::class,
		],
		'admin' => [
			Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
			Middleware\VerifyUserIsAdmin::class
		]
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth.basic' => Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
		'auth.user' => Middleware\AuthenticateUser::class,
		'auth.license' => Middleware\AuthenticateLicense::class,
		'admin' => Middleware\VerifyUserIsAdmin::class,
		'guest' => Middleware\RedirectIfAuthenticated::class,
		'helpscout.signature' => Middleware\VerifyHelpScoutSignature::class,
		'can' => Illuminate\Foundation\Http\Middleware\Authorize::class,
		'throttle' => Illuminate\Routing\Middleware\ThrottleRequests::class,
	];

}
